<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;
use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AgentInterface;
use App\Services\MCP\ProviderRouter;
use App\Services\MCP\TokenObserver;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent implements AgentInterface
{
    protected AgentDTO $config;

    protected ProviderRouter $router;

    protected TokenObserver $tokenObserver;

    protected array $history = [];

    protected array $systemPrompt = [];

    public function __construct()
    {
        $this->router = app(ProviderRouter::class);
        $this->tokenObserver = app(TokenObserver::class);
    }

    abstract public function canHandle(string $task): bool;

    public function execute(string $task, array $context = []): AIResponseDTO
    {
        $start = microtime(true);

        $this->addToHistory('user', $task);

        $prompt = $this->buildPrompt($task, $context);

        $request = new AIRequestDTO(
            prompt: $prompt,
            provider: $this->config->provider,
            model: $this->config->model,
            messages: $this->buildMessages($prompt),
            temperature: $this->config->temperature,
            maxTokens: 4096,
            sessionId: $context['session_id'] ?? null,
        );

        try {
            $response = $this->router->routeWithFallback($request);

            $this->addToHistory('assistant', $response->content);

            $latency = (microtime(true) - $start) * 1000;

            $this->tokenObserver->track(
                provider: $response->provider,
                model: $response->model,
                inputTokens: $response->inputTokens,
                outputTokens: $response->outputTokens,
                cost: $response->cost,
                latencyMs: $latency,
                sessionId: $request->sessionId ?? get_class($this),
                action: 'agent.' . class_basename($this),
            );

            return $response;
        } catch (\Throwable $e) {
            Log::error("Agent execution failed", [
                'agent' => $this->getName(),
                'task' => $task,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getName(): string
    {
        return $this->config->name ?? class_basename($this);
    }

    public function getType(): string
    {
        return $this->config->type;
    }

    public function getConfig(): AgentDTO
    {
        return $this->config;
    }

    public function setConfig(AgentDTO $config): void
    {
        $this->config = $config;
    }

    public function reset(): void
    {
        $this->history = [];
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    protected function buildPrompt(string $task, array $context): string
    {
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = "\n\nContext:\n" . json_encode($context, JSON_PRETTY_PRINT);
        }

        return "You are {$this->getName()}, a {$this->getType()} agent.\n\n{$this->systemPrompt()}\n\nTask: {$task}{$contextStr}";
    }

    abstract protected function systemPrompt(): string;

    protected function buildMessages(string $fullPrompt): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
        ];

        foreach ($this->history as $entry) {
            $messages[] = [
                'role' => $entry['role'],
                'content' => $entry['content'],
            ];
        }

        return $messages;
    }

    protected function addToHistory(string $role, string $content): void
    {
        $this->history[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function parseJsonResponse(string $content): array
    {
        $content = trim($content);

        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Agent JSON parse error", [
                'agent' => $this->getName(),
                'error' => json_last_error_msg(),
            ]);
            return ['raw_content' => $content];
        }

        return $decoded;
    }
}
