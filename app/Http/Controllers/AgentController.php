<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgentExecuteRequest;
use App\Http\Resources\AgentResource;
use App\Services\Agents\BaseAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    private array $agents = [];

    public function register(string $name, BaseAgent $agent): void
    {
        $this->agents[$name] = $agent;
    }

    public function execute(AgentExecuteRequest $request, string $agentName): JsonResponse
    {
        $agent = $this->resolveAgent($agentName);

        $response = $agent->execute(
            task: $request->input('task'),
            context: $request->input('context', []),
        );

        return response()->json([
            'data' => [
                'agent' => $agent->getName(),
                'response' => $response->content,
                'tokens' => $response->totalTokens(),
                'cost' => $response->cost,
                'latency_ms' => $response->latencyMs,
            ],
        ]);
    }

    public function list(): JsonResponse
    {
        return response()->json([
            'data' => array_map(fn($agent) => [
                'name' => $agent->getName(),
                'type' => $agent->getType(),
                'config' => $agent->getConfig()->toArray(),
            ], $this->agents),
        ]);
    }

    public function info(Request $request, string $agentName): AgentResource
    {
        $agent = $this->resolveAgent($agentName);

        return new AgentResource($agent);
    }

    public function reset(string $agentName): JsonResponse
    {
        $agent = $this->resolveAgent($agentName);
        $agent->reset();

        return response()->json([
            'message' => "Agent {$agentName} has been reset",
        ]);
    }

    private function resolveAgent(string $name): BaseAgent
    {
        if (isset($this->agents[$name])) {
            return $this->agents[$name];
        }

        $class = config("agents.agents.{$name}.class");

        if ($class && class_exists($class)) {
            $agent = app($class);
            $this->agents[$name] = $agent;
            return $agent;
        }

        abort(404, "Agent '{$name}' not found");
    }
}
