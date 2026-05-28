<?php

namespace App\Services\MCP;

use App\DTOs\TokenUsageDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenObserver
{
    private array $sessionTokens = [];

    private array $dailyTokens = [];

    public function track(
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $cost,
        float $latencyMs,
        string $sessionId,
        string $action,
    ): void {
        $totalTokens = $inputTokens + $outputTokens;

        $this->sessionTokens[$sessionId] = ($this->sessionTokens[$sessionId] ?? 0) + $totalTokens;

        $today = now()->toDateString();
        $this->dailyTokens[$today] = ($this->dailyTokens[$today] ?? 0) + $totalTokens;

        $this->checkThresholds($totalTokens, $provider, $sessionId);

        if (config('mcp.token_tracking.storage', 'database') === 'database') {
            $this->persist($provider, $model, $inputTokens, $outputTokens, $cost, $latencyMs, $sessionId, $action);
        }

        Cache::increment("mcp_tokens_daily_{$today}", $totalTokens);
        Cache::increment("mcp_tokens_session_{$sessionId}", $totalTokens);
        Cache::increment("mcp_cost_daily_{$today}", (int)($cost * 100));
    }

    public function summary(?string $sessionId = null): array
    {
        if ($sessionId) {
            return [
                'session_id' => $sessionId,
                'total_tokens' => $this->sessionTokens[$sessionId] ?? 0,
                'total_cost' => $this->sessionCost($sessionId),
            ];
        }

        $today = now()->toDateString();

        return [
            'daily_tokens' => $this->dailyTokens[$today] ?? Cache::get("mcp_tokens_daily_{$today}", 0),
            'session_tokens' => array_sum($this->sessionTokens),
            'total_sessions' => count($this->sessionTokens),
        ];
    }

    public function sessionCost(string $sessionId): float
    {
        if (config('mcp.token_tracking.storage') !== 'database') {
            return 0.0;
        }

        return DB::table('mcp_token_usage')
            ->where('session_id', $sessionId)
            ->sum('cost');
    }

    public function getSessionTokens(string $sessionId): int
    {
        return $this->sessionTokens[$sessionId] ?? 0;
    }

    public function resetSession(string $sessionId): void
    {
        unset($this->sessionTokens[$sessionId]);
        Cache::forget("mcp_tokens_session_{$sessionId}");
    }

    private function persist(
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $cost,
        float $latencyMs,
        string $sessionId,
        string $action,
    ): void {
        try {
            DB::table('mcp_token_usage')->insert([
                'provider' => $provider,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost' => $cost,
                'latency_ms' => $latencyMs,
                'session_id' => $sessionId,
                'action' => $action,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to persist token usage', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
        }
    }

    private function checkThresholds(int $tokens, string $provider, string $sessionId): void
    {
        $threshold = config('mcp.token_tracking.alert_threshold', 100000);

        if ($tokens >= $threshold) {
            Log::warning("MCP Token threshold exceeded", [
                'provider' => $provider,
                'session_id' => $sessionId,
                'tokens' => $tokens,
                'threshold' => $threshold,
            ]);
        }

        $today = now()->toDateString();
        $dailyTotal = $this->dailyTokens[$today] ?? Cache::get("mcp_tokens_daily_{$today}", 0);

        if ($dailyTotal >= $threshold * 10) {
            Log::warning("MCP Daily token limit approaching", [
                'daily_tokens' => $dailyTotal,
                'date' => $today,
            ]);
        }
    }
}
