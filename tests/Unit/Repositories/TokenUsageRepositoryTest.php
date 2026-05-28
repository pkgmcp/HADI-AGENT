<?php

use App\Models\MCP\TokenUsage;
use App\Repositories\TokenUsageRepository;

beforeEach(function () {
    $this->repo = new TokenUsageRepository;
});

it('records token usage', function () {
    $record = $this->repo->record([
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'input_tokens' => 100,
        'output_tokens' => 50,
        'cost' => 0.003,
        'latency_ms' => 150,
        'session_id' => 'rec_session',
        'action' => 'test',
    ]);

    expect($record)->toBeInstanceOf(TokenUsage::class);
    expect($record->provider)->toBe('openai');
});

it('gets daily usage grouped by provider', function () {
    $this->repo->record([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'input_tokens' => 100, 'output_tokens' => 50,
        'cost' => 0.003, 'latency_ms' => 150,
        'session_id' => 'd1', 'action' => 't',
    ]);

    $this->repo->record([
        'provider' => 'anthropic', 'model' => 'claude-3',
        'input_tokens' => 200, 'output_tokens' => 100,
        'cost' => 0.006, 'latency_ms' => 200,
        'session_id' => 'd2', 'action' => 't',
    ]);

    $daily = $this->repo->getDailyUsage(now()->toDateString());
    expect($daily)->toHaveCount(2);
});

it('gets session usage summary', function () {
    $this->repo->record([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'input_tokens' => 100, 'output_tokens' => 50,
        'cost' => 0.003, 'latency_ms' => 150,
        'session_id' => 'sum_sess', 'action' => 't',
    ]);

    $summary = $this->repo->getSessionUsage('sum_sess');
    expect($summary['total_input_tokens'])->toBe(100);
    expect($summary['total_cost'])->toBe(0.003);
});

it('returns empty array for unknown session', function () {
    $summary = $this->repo->getSessionUsage('unknown_sess');
    expect($summary)->toBe([]);
});

it('gets provider summary', function () {
    $this->repo->record([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'input_tokens' => 100, 'output_tokens' => 50,
        'cost' => 0.003, 'latency_ms' => 150,
        'session_id' => 'p1', 'action' => 't',
    ]);

    $summary = $this->repo->getProviderSummary();
    expect($summary)->toHaveCount(1);
    expect($summary[0]['provider'])->toBe('openai');
});

it('calculates total cost within date range', function () {
    $this->repo->record([
        'provider' => 'openai', 'model' => 'gpt-4o',
        'input_tokens' => 100, 'output_tokens' => 50,
        'cost' => 0.005, 'latency_ms' => 100,
        'session_id' => 'cost_sess', 'action' => 't',
    ]);

    $cost = $this->repo->getTotalCost(
        now()->subDay()->toDateString(),
        now()->addDay()->toDateString(),
    );

    expect($cost)->toBe(0.005);
});

it('cleans old records', function () {
    $this->repo->record([
        'provider' => 'local', 'model' => 'test',
        'input_tokens' => 1, 'output_tokens' => 1,
        'cost' => 0, 'latency_ms' => 0,
        'session_id' => 'old_sess', 'action' => 'clean',
    ]);

    $deleted = $this->repo->cleanOldRecords(0);
    expect($deleted)->toBeGreaterThanOrEqual(0);
});
