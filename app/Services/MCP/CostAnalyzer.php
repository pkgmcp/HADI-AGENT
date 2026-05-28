<?php

namespace App\Services\MCP;

use App\DTOs\AIResponseDTO;
use Illuminate\Support\Facades\Cache;

class CostAnalyzer
{
    private array $dailyCosts = [];

    private array $providerCosts = [];

    private array $modelCosts = [];

    public function record(AIResponseDTO $response): void
    {
        $today = now()->toDateString();

        $this->dailyCosts[$today] = ($this->dailyCosts[$today] ?? 0) + $response->cost;
        $this->providerCosts[$response->provider] = ($this->providerCosts[$response->provider] ?? 0) + $response->cost;
        $this->modelCosts[$response->model] = ($this->modelCosts[$response->model] ?? 0) + $response->cost;

        Cache::increment("mcp_cost_daily_{$today}", (int)($response->cost * 100));
        Cache::increment("mcp_cost_provider_{$response->provider}", (int)($response->cost * 100));
    }

    public function summary(): array
    {
        $today = now()->toDateString();

        return [
            'daily' => [
                $today => $this->dailyCosts[$today] ?? Cache::get("mcp_cost_daily_{$today}", 0) / 100,
            ],
            'by_provider' => $this->providerCosts,
            'by_model' => $this->modelCosts,
            'total' => array_sum($this->dailyCosts),
            'estimated_monthly' => $this->estimateMonthly(),
        ];
    }

    public function estimateMonthly(): float
    {
        $today = now()->toDateString();
        $dailyCost = $this->dailyCosts[$today] ?? Cache::get("mcp_cost_daily_{$today}", 0) / 100;

        return $dailyCost * now()->daysInMonth;
    }

    public function byProvider(string $provider): float
    {
        return $this->providerCosts[$provider] ?? 0;
    }

    public function byModel(string $model): float
    {
        return $this->modelCosts[$model] ?? 0;
    }

    public function daily(string $date): float
    {
        if (isset($this->dailyCosts[$date])) {
            return $this->dailyCosts[$date];
        }

        return Cache::get("mcp_cost_daily_{$date}", 0) / 100;
    }

    public function projection(int $days = 30): float
    {
        $total = 0;
        $count = 0;

        foreach ($this->dailyCosts as $cost) {
            $total += $cost;
            $count++;
        }

        if ($count === 0) {
            return 0;
        }

        return ($total / $count) * $days;
    }

    public function budgetUtilization(): array
    {
        $budget = config('ai.cost_management.budget_limit', 100.00);
        $spent = array_sum($this->dailyCosts);

        return [
            'budget' => $budget,
            'spent' => $spent,
            'remaining' => max(0, $budget - $spent),
            'utilization_percent' => $budget > 0 ? round(($spent / $budget) * 100, 2) : 0,
        ];
    }
}
