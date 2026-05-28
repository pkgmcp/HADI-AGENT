<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class PlannerAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'PlannerAgent',
            type: 'planner',
            provider: config('agents.agents.planner.provider', 'openai'),
            model: config('agents.agents.planner.model', 'gpt-4o'),
            temperature: config('agents.agents.planner.temperature', 0.3),
            maxIterations: config('agents.agents.planner.max_iterations', 3),
            config: config('agents.agents.planner'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['plan', 'design', 'architect', 'structure', 'organize', 'blueprint'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function createExecutionPlan(string $task, array $constraints = []): array
    {
        $response = $this->execute(
            "Create a detailed execution plan for: {$task}\n\nConstraints: " . json_encode($constraints),
            ['task' => $task, 'constraints' => $constraints]
        );

        return $this->parseJsonResponse($response->content);
    }

    public function estimateEffort(string $task): array
    {
        $response = $this->execute(
            "Estimate the effort required for this task. Provide:\n" .
            "- complexity (low/medium/high)\n- estimated hours\n- required skills\n- dependencies\n- risks\n\nTask: {$task}"
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are PlannerAgent, an expert software architect and project planner.\n\n" .
            "Your role:\n" .
            "- Analyze complex requests and break them into executable steps\n" .
            "- Design scalable architecture following clean architecture principles\n" .
            "- Identify dependencies, risks, and resource requirements\n" .
            "- Create detailed execution plans with clear milestones\n" .
            "- Estimate effort and complexity accurately\n\n" .
            "Always output structured plans in JSON format with:\n" .
            "- phases: array of phase objects with name, steps, duration, dependencies\n" .
            "- architecture: key architectural decisions\n" .
            "- risks: identified risks with mitigation strategies\n" .
            "- resources: required resources and tools";
    }
}
