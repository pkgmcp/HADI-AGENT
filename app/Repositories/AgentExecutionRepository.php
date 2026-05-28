<?php

namespace App\Repositories;

use App\Models\AI\AgentExecution;

class AgentExecutionRepository
{
    public function create(array $data): AgentExecution
    {
        return AgentExecution::create($data);
    }

    public function updateStatus(int $id, string $status, ?string $error = null): bool
    {
        $data = ['status' => $status];

        if ($error) {
            $data['error'] = $error;
        }

        return AgentExecution::findOrFail($id)->update($data);
    }

    public function findBySession(string $sessionId): iterable
    {
        return AgentExecution::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAgentStats(string $agentName): array
    {
        return AgentExecution::byAgent($agentName)
            ->selectRaw('
                COUNT(*) as total_executions,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                AVG(latency_ms) as avg_latency_ms,
                SUM(cost) as total_cost
            ')
            ->first()
            ?->toArray() ?? [];
    }

    public function getRecentExecutions(int $limit = 20): iterable
    {
        return AgentExecution::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
