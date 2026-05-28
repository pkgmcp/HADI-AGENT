<?php

use App\Interfaces\AIProviderInterface;

it('all AI providers implement the interface', function () {
    $providers = [
        App\Services\AI\OpenAIProvider::class,
        App\Services\AI\AnthropicProvider::class,
        App\Services\AI\GeminiProvider::class,
        App\Services\AI\OllamaProvider::class,
        App\Services\AI\DeepSeekProvider::class,
    ];

    foreach ($providers as $providerClass) {
        $reflection = new ReflectionClass($providerClass);
        expect($reflection->implementsInterface(AIProviderInterface::class))->toBeTrue();
    }
});

it('all providers report name', function () {
    $providers = [
        App\Services\AI\OpenAIProvider::class,
        App\Services\AI\AnthropicProvider::class,
        App\Services\AI\GeminiProvider::class,
        App\Services\AI\OllamaProvider::class,
        App\Services\AI\DeepSeekProvider::class,
    ];

    foreach ($providers as $providerClass) {
        $provider = app($providerClass);
        expect($provider->name())->toBeString();
        expect(strlen($provider->name()))->toBeGreaterThan(0);
    }
});

it('all providers have required methods', function () {
    $providers = [
        App\Services\AI\OpenAIProvider::class,
        App\Services\AI\AnthropicProvider::class,
        App\Services\AI\GeminiProvider::class,
        App\Services\AI\OllamaProvider::class,
        App\Services\AI\DeepSeekProvider::class,
    ];

    foreach ($providers as $providerClass) {
        $provider = app($providerClass);

        expect($provider->models())->toBeArray();
        expect($provider->isAvailable())->toBeBool();
        expect($provider->calculateCost(100, 100))->toBeFloat();
    }
});

it('cost calculation returns non-negative values', function () {
    $providers = [
        App\Services\AI\OpenAIProvider::class,
        App\Services\AI\AnthropicProvider::class,
        App\Services\AI\DeepSeekProvider::class,
    ];

    foreach ($providers as $providerClass) {
        $provider = app($providerClass);
        $cost = $provider->calculateCost(1000, 500);

        expect($cost)->toBeGreaterThanOrEqual(0);
    }
});

it('providers without API key are unavailable', function () {
    $provider = app(App\Services\AI\OpenAIProvider::class);

    expect($provider->isAvailable())->toBeFalse();
});
