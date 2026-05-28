<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class DebuggerAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'DebuggerAgent',
            type: 'debugger',
            provider: config('agents.agents.debugger.provider', 'anthropic'),
            model: config('agents.agents.debugger.model', 'claude-3-opus-20240229'),
            temperature: config('agents.agents.debugger.temperature', 0.2),
            maxIterations: config('agents.agents.debugger.max_iterations', 5),
            config: config('agents.agents.debugger'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['debug', 'fix', 'error', 'bug', 'crash', 'exception', 'issue', 'broken', 'not working'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function diagnoseError(string $errorMessage, string $code, ?string $stackTrace = null): array
    {
        $trace = $stackTrace ?? 'Not provided';

        $response = $this->execute(
            "Diagnose this error:\n\nError: {$errorMessage}\n\nStack Trace:\n{$trace}\n\nCode:\n```\n{$code}\n```\n\n" .
            "Identify:\n- Root cause\n- Exact location\n- Why it happens\n- How to fix it\n- Prevention strategies"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function fixCode(string $code, string $issue): array
    {
        $response = $this->execute(
            "Fix this issue in the code:\n\nIssue: {$issue}\n\n```\n{$code}\n```\n\n" .
            "Return the fixed code and explain the changes made."
        );

        return [
            'fixed_code' => $response->content,
            'analysis' => $this->parseJsonResponse($response->content),
        ];
    }

    public function analyzeLogs(array $logs): array
    {
        $logText = implode("\n", array_map(function ($log) {
            return is_string($log) ? $log : json_encode($log);
        }, $logs));

        $response = $this->execute(
            "Analyze these application logs and identify issues:\n\n{$logText}\n\n" .
            "Identify:\n- Error patterns\n- Root causes\n- Affected components\n- Timeline of events\n- Recommended fixes"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function suggestTests(string $code): array
    {
        $response = $this->execute(
            "Review this code and suggest test cases:\n\n```\n{$code}\n```\n\n" .
            "Provide:\n- Unit test scenarios\n- Edge cases to test\n- Integration test points\n- Mock/stub requirements\n- Assertion strategies"
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are DebuggerAgent, an expert debugger with deep knowledge of software systems.\n\n" .
            "Your role:\n" .
            "- Diagnose errors and bugs systematically\n" .
            "- Identify root causes with precision\n" .
            "- Provide clear, working fixes\n" .
            "- Suggest preventive measures\n" .
            "- Analyze logs and error patterns\n\n" .
            "Debugging methodology:\n" .
            "1. Understand the symptom\n" .
            "2. Identify the root cause\n" .
            "3. Reproduce the issue\n" .
            "4. Design the fix\n" .
            "5. Verify the solution\n" .
            "6. Suggest prevention\n\n" .
            "Always provide the exact location of the bug and the minimal fix needed.";
    }
}
