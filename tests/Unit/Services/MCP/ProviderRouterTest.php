<?php

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use App\Services\MCP\ProviderRouter;

beforeEach(function () {
    $this->router = new ProviderRouter;
});

it('registers and resolves providers', function () {
    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('name')->andReturn('test');
    $provider->shouldReceive('isAvailable')->andReturn(true);

    $this->router->register('test', $provider);

    $resolved = $this->router->resolveProvider('test');
    expect($resolved)->toBe($provider);
});

it('falls back to available provider when preferred is unavailable', function () {
    $unavailable = Mockery::mock(AIProviderInterface::class);
    $unavailable->shouldReceive('name')->andReturn('provider_a');
    $unavailable->shouldReceive('isAvailable')->andReturn(false);

    $available = Mockery::mock(AIProviderInterface::class);
    $available->shouldReceive('name')->andReturn('provider_b');
    $available->shouldReceive('isAvailable')->andReturn(true);

    $this->router->register('provider_a', $unavailable);
    $this->router->register('provider_b', $available);

    $resolved = $this->router->resolveProvider('provider_a');
    expect($resolved)->toBe($available);
});

it('throws exception when no providers available', function () {
    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('isAvailable')->andReturn(false);

    $this->router->register('test', $provider);

    $this->router->resolveProvider('test');
})->throws(\RuntimeException::class);

it('routes to provider and returns response', function () {
    $request = new AIRequestDTO(
        prompt: 'Hello',
        provider: 'openai',
        model: 'gpt-4o',
    );

    $expectedResponse = new AIResponseDTO(
        content: 'Hi there',
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 5,
        outputTokens: 5,
        cost: 0.0001,
        latencyMs: 100,
    );

    $provider = Mockery::mock(AIProviderInterface::class);
    $provider->shouldReceive('name')->andReturn('openai');
    $provider->shouldReceive('isAvailable')->andReturn(true);
    $provider->shouldReceive('send')->with($request)->andReturn($expectedResponse);

    $this->router->register('openai', $provider);

    $response = $this->router->route($request);
    expect($response)->toBe($expectedResponse);
});
