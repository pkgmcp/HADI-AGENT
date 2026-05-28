<?php

use App\DTOs\AgentDTO;

it('creates an agent DTO from array', function () {
    $dto = AgentDTO::fromArray([
        'name' => 'TestAgent',
        'type' => 'planner',
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'temperature' => 0.3,
        'max_iterations' => 5,
    ]);

    expect($dto->name)->toBe('TestAgent');
    expect($dto->type)->toBe('planner');
    expect($dto->provider)->toBe('openai');
    expect($dto->temperature)->toBe(0.3);
    expect($dto->maxIterations)->toBe(5);
});

it('converts agent DTO to array', function () {
    $dto = new AgentDTO(
        name: 'TestAgent',
        type: 'coder',
        provider: 'anthropic',
        model: 'claude-3',
        temperature: 0.2,
        maxIterations: 3,
        config: ['key' => 'value'],
        id: '123',
    );

    $array = $dto->toArray();

    expect($array['name'])->toBe('TestAgent');
    expect($array['type'])->toBe('coder');
    expect($array['config'])->toBe(['key' => 'value']);
    expect($array['id'])->toBe('123');
});

it('uses defaults for optional fields', function () {
    $dto = AgentDTO::fromArray([
        'name' => 'Minimal',
        'type' => 'worker',
        'provider' => 'openai',
        'model' => 'gpt-3.5',
    ]);

    expect($dto->temperature)->toBe(0.7);
    expect($dto->maxIterations)->toBe(3);
    expect($dto->config)->toBe([]);
    expect($dto->id)->toBeNull();
});
