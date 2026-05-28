<?php

use App\DTOs\AIResponseDTO;

it('creates AI response DTO', function () {
    $dto = new AIResponseDTO(
        content: 'Hello world',
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 10,
        outputTokens: 20,
        cost: 0.001,
        latencyMs: 150.5,
    );

    expect($dto->content)->toBe('Hello world');
    expect($dto->totalTokens())->toBe(30);
});

it('converts AI response to array', function () {
    $dto = AIResponseDTO::fromArray([
        'content' => 'Test response',
        'provider' => 'anthropic',
        'model' => 'claude-3',
        'input_tokens' => 50,
        'output_tokens' => 100,
        'cost' => 0.005,
        'latency_ms' => 200,
    ]);

    $array = $dto->toArray();

    expect($array['content'])->toBe('Test response');
    expect($array['input_tokens'])->toBe(50);
    expect($array['total_tokens'] ?? $dto->totalTokens())->toBe(150);
});

it('calculates total tokens correctly', function () {
    $dto = AIResponseDTO::fromArray([
        'content' => 'Test',
        'provider' => 'openai',
        'model' => 'gpt-4',
        'input_tokens' => 100,
        'output_tokens' => 200,
        'cost' => 0.01,
        'latency_ms' => 300,
    ]);

    expect($dto->totalTokens())->toBe(300);
});
