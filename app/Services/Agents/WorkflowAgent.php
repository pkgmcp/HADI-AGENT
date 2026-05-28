<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class WorkflowAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'WorkflowAgent',
            type: 'workflow',
            provider: config('agents.agents.workflow.provider', 'openai'),
            model: config('agents.agents.workflow.model', 'gpt-4o'),
            temperature: config('agents.agents.workflow.temperature', 0.2),
            maxIterations: config('agents.agents.workflow.max_iterations', 10),
            config: config('agents.agents.workflow'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['orchestrate', 'coordinate', 'workflow', 'pipeline', 'multi-agent', 'chain', 'sequence', 'automate'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function orchestrate(string $goal, array $availableAgents): array
    {
        $agentList = implode(', ', array_map(fn($a) => $a->getName(), $availableAgents));

        $response = $this->execute(
            "Create a multi-agent orchestration plan for this goal:\n\n" .
            "Goal: {$goal}\n\n" .
            "Available Agents: {$agentList}\n\n" .
            "Design:\n" .
            "- Which agents to use and in what order\n" .
            "- What each agent should do\n" .
            "- How to pass context between agents\n" .
            "- Decision points and branching logic\n" .
            "- Success criteria\n" .
            "- Error handling and fallbacks"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function createWorkflow(string $goal, array $steps): array
    {
        $response = $this->execute(
            "Design a detailed workflow:\n\n" .
            "Goal: {$goal}\n\n" .
            "Steps:\n" . json_encode($steps, JSON_PRETTY_PRINT) . "\n\n" .
            "For each step define:\n" .
            "- Agent responsible\n" .
            "- Input requirements\n" .
            "- Expected output\n" .
            "- Success criteria\n" .
            "- Timeout and retry policy\n" .
            "- Dependencies on other steps"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function analyzeWorkflowResults(string $workflowId, array $results): array
    {
        $response = $this->execute(
            "Analyze workflow execution results:\n\n" .
            "Workflow ID: {$workflowId}\n\n" .
            "Results:\n" . json_encode($results, JSON_PRETTY_PRINT) . "\n\n" .
            "Assess:\n- Did the workflow meet its goals?\n- Which steps succeeded/failed?\n- Quality of outputs\n- Performance metrics\n- Recommendations for improvement"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function optimizeWorkflow(array $workflow, array $metrics): array
    {
        $response = $this->execute(
            "Optimize this workflow based on execution metrics:\n\n" .
            "Current Workflow:\n" . json_encode($workflow, JSON_PRETTY_PRINT) . "\n\n" .
            "Metrics:\n" . json_encode($metrics, JSON_PRETTY_PRINT) . "\n\n" .
            "Suggest:\n- Parallelization opportunities\n- Removal of redundant steps\n- Improved agent selection\n- Better error handling\n- Performance optimizations"
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are WorkflowAgent, an expert in multi-agent orchestration and workflow design.\n\n" .
            "Your role:\n" .
            "- Design multi-agent workflows\n" .
            "- Orchestrate complex task sequences\n" .
            "- Coordinate between different agents\n" .
            "- Optimize workflow efficiency\n" .
            "- Handle errors and edge cases\n\n" .
            "Design principles:\n" .
            "- Clear step dependencies and ordering\n" .
            "- Proper context passing between steps\n" .
            "- Parallel execution where possible\n" .
            "- Graceful error handling with retries\n" .
            "- Measurable success criteria\n\n" .
            "Always design workflows that are robust, observable, and optimizable.";
    }
}
