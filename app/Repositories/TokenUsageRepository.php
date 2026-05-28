<?php

namespace App\Repositories;

use App\Models\MCP\TokenUsage;

class TokenUsageRepository
{
    public function record(array $data): TokenUsage
    {
        return TokenUsage::create($data);
    }

    public function getDailyUsage(string $date): array
    {
        return TokenUsage::whereDate('created_at', $date)
            ->selectRaw('
                provider,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as total_cost,
                COUNT(*) as request_count
            ')
            ->groupBy('provider')
            ->get()
            ->toArray();
    }

    public function getSessionUsage(string $sessionId): array
    {
        return TokenUsage::bySession($sessionId)
            ->selectRaw('
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as total_cost,
                AVG(latency_ms) as avg_latency_ms,
                COUNT(*) as request_count
            ')
            ->first()
            ?->toArray() ?? [];
    }

    public function getProviderSummary(): array
    {
        return TokenUsage::selectRaw('
                provider,
                SUM(input_tokens + output_tokens) as total_tokens,
                SUM(cost) as total_cost,
                COUNT(*) as request_count,
                AVG(latency_ms) as avg_latency_ms
            ')
            ->groupBy('provider')
            ->get()
            ->toArray();
    }

    public function getTotalCost(string $startDate = null, string $endDate = null): float
    {
        $query = TokenUsage::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return (float) $query->sum('cost');
    }

    public function cleanOldRecords(int $daysOld = 90): int
    {
        return TokenUsage::where('created_at', '<', now()->subDays($daysOld))->delete();
    }
}
