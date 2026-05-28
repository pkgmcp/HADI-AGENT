<?php

use App\DTOs\AIResponseDTO;
use App\Services\MCP\CostAnalyzer;

beforeEach(function () {
    $this->analyzer = new CostAnalyzer;
});

it('records cost from AI response', function () {
    $response = new AIResponseDTO(
        content: 'test',
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 100,
        outputTokens: 50,
        cost: 0.002,
        latencyMs: 150,
    );

    $this->analyzer->record($response);

    $summary = $this->analyzer->summary();
    expect($summary['by_provider']['openai'])->toBe(0.002);
    expect($summary['total'])->toBe(0.002);
});

it('tracks multiple provider costs separately', function () {
    $openai = new AIResponseDTO('a', 'openai', 'gpt-4o', 100, 50, 0.002, 100);
    $anthropic = new AIResponseDTO('b', 'anthropic', 'claude-3', 200, 100, 0.005, 200);

    $this->analyzer->record($openai);
    $this->analyzer->record($anthropic);

    expect($this->analyzer->byProvider('openai'))->toBe(0.002);
    expect($this->analyzer->byProvider('anthropic'))->toBe(0.005);
    expect($this->analyzer->byModel('gpt-4o'))->toBe(0.002);
});

it('returns zero for unknown provider', function () {
    expect($this->analyzer->byProvider('nonexistent'))->toBe(0.0);
});

it('estimates monthly cost based on daily average', function () {
    $response = new AIResponseDTO('a', 'openai', 'gpt-4o', 100, 50, 0.001, 100);

    // Record same cost 10 times to simulate daily usage
    for ($i = 0; $i < 10; $i++) {
        $this->analyzer->record($response);
    }

    $monthly = $this->analyzer->estimateMonthly();
    expect($monthly)->toBeGreaterThan(0);
});

it('calculates budget utilization', function () {
    config(['ai.cost_management.budget_limit' => 100.00]);

    $response = new AIResponseDTO('a', 'openai', 'gpt-4o', 100, 50, 10.00, 100);
    $this->analyzer->record($response);

    $util = $this->analyzer->budgetUtilization();

    expect($util['budget'])->toBe(100.00);
    expect($util['spent'])->toBe(10.00);
    expect($util['remaining'])->toBe(90.00);
    expect($util['utilization_percent'])->toBe(10.0);
});

it('returns zero projection with no data', function () {
    expect($this->analyzer->projection(30))->toBe(0.0);
});

it('accumulates daily costs correctly', function () {
    $today = now()->toDateString();

    $r1 = new AIResponseDTO('a', 'openai', 'gpt-4o', 10, 5, 0.001, 50);
    $r2 = new AIResponseDTO('b', 'openai', 'gpt-4o', 20, 10, 0.002, 60);

    $this->analyzer->record($r1);
    $this->analyzer->record($r2);

    expect($this->analyzer->daily($today))->toBe(0.003);
});

it('returns zero cost for unmetered providers', function () {
    $response = new AIResponseDTO('a', 'ollama', 'llama2', 100, 50, 0.0, 100);
    $this->analyzer->record($response);

    expect($this->analyzer->byProvider('ollama'))->toBe(0.0);
});
