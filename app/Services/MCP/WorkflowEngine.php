<?php

namespace App\Services\MCP;

use App\DTOs\WorkflowDTO;
use App\Interfaces\WorkflowEngineInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WorkflowEngine implements WorkflowEngineInterface
{
    private MCPServer $mcpServer;

    private array $activeWorkflows = [];

    public function __construct()
    {
        $this->mcpServer = app(MCPServer::class);
    }

    public function create(WorkflowDTO $workflow): string
    {
        $id = $workflow->id ?? 'wf_' . bin2hex(random_bytes(16));

        $this->activeWorkflows[$id] = [
            'dto' => $workflow,
            'status' => 'created',
            'current_step' => 0,
            'results' => [],
            'errors' => [],
            'started_at' => null,
            'completed_at' => null,
        ];

        $this->persist($id);

        return $id;
    }

    public function execute(string $workflowId): array
    {
        $workflow = $this->load($workflowId);

        if (!$workflow) {
            throw new \RuntimeException("Workflow {$workflowId} not found");
        }

        $workflow['status'] = 'running';
        $workflow['started_at'] = now()->toIso8601String();
        $this->activeWorkflows[$workflowId] = $workflow;

        $dto = $workflow['dto'];
        $results = [];

        foreach ($dto->steps as $index => $step) {
            $workflow['current_step'] = $index;

            try {
                $result = $this->executeStep($step, $results, $dto->context);
                $results[$step['name'] ?? "step_{$index}"] = $result;

                $workflow['results'] = $results;
                $this->persist($workflowId);
            } catch (\Throwable $e) {
                Log::error("Workflow step {$index} failed", [
                    'workflow_id' => $workflowId,
                    'step' => $step,
                    'error' => $e->getMessage(),
                ]);

                $workflow['errors'][] = [
                    'step' => $step['name'] ?? "step_{$index}",
                    'error' => $e->getMessage(),
                    'index' => $index,
                ];

                $workflow['status'] = 'failed';
                $this->activeWorkflows[$workflowId] = $workflow;
                $this->persist($workflowId);

                if (!($step['continue_on_failure'] ?? false)) {
                    break;
                }
            }
        }

        if ($workflow['status'] !== 'failed') {
            $workflow['status'] = 'completed';
        }

        $workflow['completed_at'] = now()->toIso8601String();
        $this->activeWorkflows[$workflowId] = $workflow;
        $this->persist($workflowId);

        return [
            'workflow_id' => $workflowId,
            'status' => $workflow['status'],
            'steps_completed' => count($results),
            'total_steps' => count($dto->steps),
            'results' => $results,
            'errors' => $workflow['errors'],
        ];
    }

    public function step(string $workflowId, string $stepName): array
    {
        $workflow = $this->load($workflowId);

        if (!$workflow) {
            throw new \RuntimeException("Workflow {$workflowId} not found");
        }

        $results = $workflow['results'];

        if (!isset($results[$stepName])) {
            return [
                'found' => false,
                'workflow_id' => $workflowId,
                'step' => $stepName,
            ];
        }

        return [
            'found' => true,
            'workflow_id' => $workflowId,
            'step' => $stepName,
            'result' => $results[$stepName],
        ];
    }

    public function status(string $workflowId): array
    {
        $workflow = $this->load($workflowId);

        if (!$workflow) {
            return ['found' => false, 'workflow_id' => $workflowId];
        }

        return [
            'found' => true,
            'workflow_id' => $workflowId,
            'status' => $workflow['status'],
            'current_step' => $workflow['current_step'],
            'total_steps' => count($workflow['dto']->steps),
            'errors_count' => count($workflow['errors']),
            'results_count' => count($workflow['results']),
            'started_at' => $workflow['started_at'],
            'completed_at' => $workflow['completed_at'],
        ];
    }

    public function cancel(string $workflowId): bool
    {
        $workflow = $this->load($workflowId);

        if (!$workflow || $workflow['status'] === 'completed') {
            return false;
        }

        $workflow['status'] = 'cancelled';
        $workflow['completed_at'] = now()->toIso8601String();
        $this->activeWorkflows[$workflowId] = $workflow;
        $this->persist($workflowId);

        return true;
    }

    public function retry(string $workflowId): array
    {
        $workflow = $this->load($workflowId);

        if (!$workflow) {
            throw new \RuntimeException("Workflow {$workflowId} not found");
        }

        $workflow['status'] = 'retrying';
        $workflow['errors'] = [];
        $workflow['results'] = [];
        $workflow['current_step'] = 0;
        $this->activeWorkflows[$workflowId] = $workflow;

        return $this->execute($workflowId);
    }

    public function getActiveWorkflows(): array
    {
        return array_filter($this->activeWorkflows, fn($w) => in_array($w['status'], ['running', 'created', 'retrying']));
    }

    public function cleanup(int $olderThanHours = 24): int
    {
        $count = 0;
        $cutoff = now()->subHours($olderThanHours);

        foreach ($this->activeWorkflows as $id => $workflow) {
            if ($workflow['completed_at'] && $cutoff->isAfter($workflow['completed_at'])) {
                unset($this->activeWorkflows[$id]);
                Cache::forget("workflow_{$id}");
                $count++;
            }
        }

        return $count;
    }

    private function executeStep(array $step, array $previousResults, array $context): mixed
    {
        $type = $step['type'] ?? 'prompt';

        return match ($type) {
            'prompt' => $this->executePromptStep($step, $previousResults, $context),
            'function' => $this->executeFunctionStep($step, $previousResults),
            'condition' => $this->executeConditionStep($step, $previousResults),
            'parallel' => $this->executeParallelStep($step, $previousResults, $context),
            'agent' => $this->executeAgentStep($step, $previousResults, $context),
            default => throw new \RuntimeException("Unknown step type: {$type}"),
        };
    }

    private function executePromptStep(array $step, array $previousResults, array $context): mixed
    {
        $prompt = $this->interpolate($step['prompt'] ?? '', $previousResults, $context);

        $message = new \App\DTOs\MCPMessageDTO(
            role: 'user',
            content: $prompt,
            metadata: $step['config'] ?? [],
        );

        $response = $this->mcpServer->handle($message);

        return [
            'content' => $response->content,
            'tokens' => $response->totalTokens(),
            'cost' => $response->cost,
        ];
    }

    private function executeFunctionStep(array $step, array $previousResults): mixed
    {
        $function = $step['function'] ?? null;

        if (!$function || !class_exists($function)) {
            throw new \RuntimeException("Function step not callable: {$function}");
        }

        $args = $step['arguments'] ?? [];
        foreach ($args as $key => $value) {
            if (is_string($value) && str_starts_with($value, '$')) {
                $args[$key] = $previousResults[substr($value, 1)] ?? $value;
            }
        }

        return app()->call($function, $args);
    }

    private function executeConditionStep(array $step, array $previousResults): mixed
    {
        $condition = $step['condition'] ?? '';
        $value = $previousResults[$step['source'] ?? ''] ?? null;

        $result = match ($condition) {
            'empty' => empty($value),
            'not_empty' => !empty($value),
            'true' => $value === true || $value === 'true',
            'false' => $value === false || $value === 'false',
            default => $value === $condition,
        };

        return [
            'condition' => $condition,
            'value' => $value,
            'result' => $result,
            'next' => $result ? ($step['on_true'] ?? null) : ($step['on_false'] ?? null),
        ];
    }

    private function executeParallelStep(array $step, array $previousResults, array $context): mixed
    {
        $subSteps = $step['steps'] ?? [];
        $results = [];

        foreach ($subSteps as $subStep) {
            $results[] = $this->executeStep($subStep, $previousResults, $context);
        }

        return $results;
    }

    private function executeAgentStep(array $step, array $previousResults, array $context): mixed
    {
        $agentName = $step['agent'] ?? null;
        $task = $this->interpolate($step['task'] ?? '', $previousResults, $context);

        if (!$agentName) {
            throw new \RuntimeException('Agent step requires an agent name');
        }

        $agentClass = config("agents.agents.{$agentName}.class");

        if (!$agentClass || !class_exists($agentClass)) {
            throw new \RuntimeException("Agent not found: {$agentName}");
        }

        $agent = app($agentClass);

        return $agent->execute($task, $context)->toArray();
    }

    private function interpolate(string $text, array $results, array $context): string
    {
        $text = preg_replace_callback('/\{\{results\.(\w+)\}\}/', function ($matches) use ($results) {
            return $results[$matches[1]]['content'] ?? $matches[0];
        }, $text);

        $text = preg_replace_callback('/\{\{context\.(\w+)\}\}/', function ($matches) use ($context) {
            return $context[$matches[1]] ?? $matches[0];
        }, $text);

        return $text;
    }

    private function persist(string $id): void
    {
        Cache::put("workflow_{$id}", $this->activeWorkflows[$id] ?? [], now()->addDay());
    }

    private function load(string $id): ?array
    {
        if (isset($this->activeWorkflows[$id])) {
            return $this->activeWorkflows[$id];
        }

        $cached = Cache::get("workflow_{$id}");

        if ($cached) {
            $this->activeWorkflows[$id] = $cached;
        }

        return $cached;
    }
}
