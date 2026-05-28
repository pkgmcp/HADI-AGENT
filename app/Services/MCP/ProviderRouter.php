<?php

namespace App\Services\MCP;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Log;

class ProviderRouter
{
    private array $providers = [];

    private array $latencyCache = [];

    public function register(string $name, AIProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function route(AIRequestDTO $request): AIResponseDTO
    {
        $provider = $this->resolveProvider($request->provider);

        $start = microtime(true);
        $response = $provider->send($request);
        $latency = (microtime(true) - $start) * 1000;

        $this->recordLatency($provider->name(), $latency);

        return $response;
    }

    public function routeWithFallback(AIRequestDTO $request): AIResponseDTO
    {
        $attempts = [];
        $providers = $this->getOrderedProviders($request->provider);

        foreach ($providers as $name => $provider) {
            try {
                $start = microtime(true);
                $response = $provider->send($request);
                $latency = (microtime(true) - $start) * 1000;

                $this->recordLatency($name, $latency);

                return $response;
            } catch (\Throwable $e) {
                Log::warning("Provider {$name} failed", [
                    'error' => $e->getMessage(),
                    'attempts' => $attempts,
                ]);

                $attempts[] = $name;

                if (!$provider->isAvailable()) {
                    continue;
                }
            }
        }

        throw new \RuntimeException(
            'All AI providers failed. Attempted: ' . implode(', ', $attempts)
        );
    }

    public function resolveProvider(string $preferred): AIProviderInterface
    {
        if (isset($this->providers[$preferred]) && $this->providers[$preferred]->isAvailable()) {
            return $this->providers[$preferred];
        }

        $strategy = config('ai.routing.strategy', 'latency');

        return match ($strategy) {
            'latency' => $this->resolveByLatency(),
            'cost' => $this->resolveByCost(),
            'random' => $this->resolveRandom(),
            default => $this->resolveByLatency(),
        };
    }

    private function resolveByLatency(): AIProviderInterface
    {
        $available = array_filter($this->providers, fn($p) => $p->isAvailable());

        if (empty($available)) {
            throw new \RuntimeException('No available AI providers');
        }

        usort($available, function ($a, $b) {
            $aLatency = $this->latencyCache[$a->name()] ?? PHP_FLOAT_MAX;
            $bLatency = $this->latencyCache[$b->name()] ?? PHP_FLOAT_MAX;
            return $aLatency <=> $bLatency;
        });

        return $available[0];
    }

    private function resolveByCost(): AIProviderInterface
    {
        $available = array_filter($this->providers, fn($p) => $p->isAvailable());

        if (empty($available)) {
            throw new \RuntimeException('No available AI providers');
        }

        usort($available, function ($a, $b) {
            return $a->calculateCost(1000, 1000) <=> $b->calculateCost(1000, 1000);
        });

        return $available[0];
    }

    private function resolveRandom(): AIProviderInterface
    {
        $available = array_filter($this->providers, fn($p) => $p->isAvailable());

        if (empty($available)) {
            throw new \RuntimeException('No available AI providers');
        }

        return $available[array_rand($available)];
    }

    private function getOrderedProviders(string $preferred): array
    {
        if (isset($this->providers[$preferred])) {
            $ordered = [$preferred => $this->providers[$preferred]];
            foreach ($this->providers as $name => $provider) {
                if ($name !== $preferred) {
                    $ordered[$name] = $provider;
                }
            }
            return $ordered;
        }

        return $this->providers;
    }

    private function recordLatency(string $provider, float $latencyMs): void
    {
        $this->latencyCache[$provider] = $latencyMs;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getAvailableProviders(): array
    {
        return array_filter($this->providers, fn($p) => $p->isAvailable());
    }

    public function isAvailable(string $name): bool
    {
        return isset($this->providers[$name]) && $this->providers[$name]->isAvailable();
    }
}
