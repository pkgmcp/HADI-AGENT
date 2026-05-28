<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class CoderAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'CoderAgent',
            type: 'coder',
            provider: config('agents.agents.coder.provider', 'anthropic'),
            model: config('agents.agents.coder.model', 'claude-3-opus-20240229'),
            temperature: config('agents.agents.coder.temperature', 0.2),
            maxIterations: config('agents.agents.coder.max_iterations', 5),
            config: config('agents.agents.coder'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['write', 'generate', 'implement', 'create code', 'develop', 'build'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function generateFile(string $path, string $specification): array
    {
        $response = $this->execute(
            "Generate production-ready code for file: {$path}\n\n" .
            "Specification:\n{$specification}\n\n" .
            "Return ONLY the file content without any explanation."
        );

        return [
            'path' => $path,
            'content' => $response->content,
            'language' => $this->detectLanguage($path),
        ];
    }

    public function refactorCode(string $code, string $instructions): array
    {
        $response = $this->execute(
            "Refactor the following code according to these instructions.\n\n" .
            "Instructions: {$instructions}\n\n```\n{$code}\n```\n\n" .
            "Return the complete refactored code only."
        );

        return [
            'original' => $code,
            'refactored' => $response->content,
            'changes' => $this->parseJsonResponse($response->content),
        ];
    }

    public function explainCode(string $code): string
    {
        $response = $this->execute(
            "Explain the following code in detail:\n\n```\n{$code}\n```"
        );

        return $response->content;
    }

    protected function systemPrompt(): string
    {
        return "You are CoderAgent, an expert software engineer specializing in production-grade code generation.\n\n" .
            "Your role:\n" .
            "- Generate clean, maintainable, production-ready code\n" .
            "- Follow SOLID principles and clean architecture\n" .
            "- Use latest language features and best practices\n" .
            "- Include proper error handling and validation\n" .
            "- Write efficient and performant code\n" .
            "- Follow established project conventions\n\n" .
            "Rules:\n" .
            "- Never put business logic in controllers\n" .
            "- Use service layer, DTOs, and repository patterns\n" .
            "- Include proper type hints and docblocks\n" .
            "- Follow PSR standards for PHP code\n" .
            "- Generate comprehensive but clean code without unnecessary comments";
    }

    private function detectLanguage(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return match ($ext) {
            'php' => 'PHP',
            'js' => 'JavaScript',
            'ts' => 'TypeScript',
            'vue' => 'Vue.js',
            'blade.php' => 'Blade',
            'sql' => 'SQL',
            'yaml', 'yml' => 'YAML',
            'json' => 'JSON',
            'css' => 'CSS',
            'scss' => 'SCSS',
            default => 'Unknown',
        };
    }
}
