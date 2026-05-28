<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class ReviewAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'ReviewAgent',
            type: 'reviewer',
            provider: config('agents.agents.reviewer.provider', 'openai'),
            model: config('agents.agents.reviewer.model', 'gpt-4o'),
            temperature: config('agents.agents.reviewer.temperature', 0.1),
            maxIterations: config('agents.agents.reviewer.max_iterations', 3),
            config: config('agents.agents.reviewer'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['review', 'audit', 'inspect', 'check', 'quality', 'validate'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function reviewCode(string $code, string $language = 'php'): array
    {
        $response = $this->execute(
            "Review this {$language} code:\n\n```{$language}\n{$code}\n```\n\n" .
            "Check for:\n- SOLID principles violations\n- Code smells and anti-patterns\n- Performance issues\n- Security vulnerabilities\n- Naming conventions\n- Architecture issues\n- Missing error handling\n- Testability issues\n\n" .
            "Return a structured review with severity ratings."
        );

        return $this->parseJsonResponse($response->content);
    }

    public function reviewArchitecture(string $description, array $files): array
    {
        $response = $this->execute(
            "Review this architecture:\n\nDescription: {$description}\n\nFiles:\n" .
            json_encode($files, JSON_PRETTY_PRINT) . "\n\n" .
            "Assess:\n- Clean architecture compliance\n- Separation of concerns\n- Dependency direction\n- Scalability\n- Maintainability\n- Design pattern usage"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function checkSecurity(string $code): array
    {
        $response = $this->execute(
            "Perform a security audit on this code:\n\n```\n{$code}\n```\n\n" .
            "Check for:\n- SQL injection\n- XSS vulnerabilities\n- CSRF\n- Authentication bypasses\n- Authorization flaws\n- Sensitive data exposure\n- Input validation issues\n- Insecure deserialization\n\n" .
            "Rate each finding: critical, high, medium, low."
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are ReviewAgent, an expert code reviewer with deep knowledge of software engineering best practices.\n\n" .
            "Your role:\n" .
            "- Perform thorough code reviews with actionable feedback\n" .
            "- Detect bugs, code smells, and anti-patterns\n" .
            "- Identify security vulnerabilities\n" .
            "- Assess architecture quality and adherence to SOLID principles\n" .
            "- Provide constructive, specific improvement suggestions\n\n" .
            "Rate findings by severity:\n" .
            "- CRITICAL: Must fix before deployment\n" .
            "- HIGH: Should fix as soon as possible\n" .
            "- MEDIUM: Consider fixing in current sprint\n" .
            "- LOW: Improvement opportunity\n\n" .
            "Always be thorough but constructive. Focus on what matters.";
    }
}
