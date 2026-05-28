<?php

namespace App\Traits;

trait HasTokens
{
    protected int $totalTokensUsed = 0;

    protected float $totalCost = 0;

    public function addTokens(int $input, int $output, float $cost): void
    {
        $this->totalTokensUsed += $input + $output;
        $this->totalCost += $cost;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokensUsed;
    }

    public function getTotalCost(): float
    {
        return $this->totalCost;
    }

    public function resetTokens(): void
    {
        $this->totalTokensUsed = 0;
        $this->totalCost = 0;
    }

    public function tokenSummary(): array
    {
        return [
            'total_tokens' => $this->totalTokensUsed,
            'total_cost' => $this->totalCost,
        ];
    }
}
