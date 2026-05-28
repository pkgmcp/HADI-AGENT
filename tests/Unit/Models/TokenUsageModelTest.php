<?php

use App\Models\MCP\TokenUsage;

it('has fillable attributes', function () {
    $model = new TokenUsage;

    expect($model->getFillable())->toContain('provider');
    expect($model->getFillable())->toContain('session_id');
    expect($model->getFillable())->toContain('cost');
});

it('casts attributes correctly', function () {
    $model = new TokenUsage;

    expect($model->getCasts()['input_tokens'])->toBe('integer');
    expect($model->getCasts()['output_tokens'])->toBe('integer');
    expect($model->getCasts()['cost'])->toBe('float');
    expect($model->getCasts()['latency_ms'])->toBe('float');
    expect($model->getCasts()['metadata'])->toBe('array');
});

it('scopes by provider', function () {
    TokenUsage::factory()->create(['provider' => 'openai']);
    TokenUsage::factory()->create(['provider' => 'anthropic']);

    expect(TokenUsage::byProvider('openai')->count())->toBe(1);
});

it('scopes by session', function () {
    TokenUsage::factory()->count(2)->create(['session_id' => 'sess_1']);
    TokenUsage::factory()->create(['session_id' => 'sess_2']);

    expect(TokenUsage::bySession('sess_1')->count())->toBe(2);
});

it('scopes by action', function () {
    TokenUsage::factory()->count(3)->create(['action' => 'mcp.handle']);

    expect(TokenUsage::byAction('mcp.handle')->count())->toBe(3);
});

it('scopes to today', function () {
    TokenUsage::factory()->create();

    expect(TokenUsage::today()->count())->toBeGreaterThanOrEqual(1);
});

it('creates record with valid data', function () {
    $record = TokenUsage::create([
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'input_tokens' => 100,
        'output_tokens' => 50,
        'cost' => 0.003,
        'latency_ms' => 150,
        'session_id' => 'create_test',
        'action' => 'test_create',
    ]);

    expect($record->exists)->toBeTrue();
    expect($record->provider)->toBe('openai');
});
