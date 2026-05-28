<?php

namespace App\Services\MCP;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\DTOs\MCPMessageDTO;
use App\Interfaces\AIProviderInterface;
use App\Interfaces\MCPServerInterface;
use Illuminate\Support\Facades\Log;

class MCPServer implements MCPServerInterface
{
    private ProviderRouter $router;

    private TokenObserver $tokenObserver;

    private CostAnalyzer $costAnalyzer;

    private ContextManager $contextManager;

    private PromptReducer $promptReducer;

    private array $middleware = [];

    public function __construct()
    {
        $this->router = app(ProviderRouter::class);
        $this->tokenObserver = app(TokenObserver::class);
        $this->costAnalyzer = app(CostAnalyzer::class);
        $this->contextManager = app(ContextManager::class);
        $this->promptReducer = app(PromptReducer::class);
    }

    public function handle(MCPMessageDTO $message): AIResponseDTO
    {
        $start = microtime(true);

        $context = $this->contextManager->build($message);

        $processedMessage = $this->processMiddleware('incoming', $message);

        if (config('mcp.context_management.compression_enabled', true)) {
            $context = $this->promptReducer->reduce($context);
        }

        $request = new AIRequestDTO(
            prompt: $processedMessage->content,
            provider: $this->resolveProvider($processedMessage),
            model: $this->resolveModel($processedMessage),
            messages: $context,
            temperature: $processedMessage->metadata['temperature'] ?? 0.7,
            maxTokens: $processedMessage->metadata['max_tokens'] ?? 4096,
            sessionId: $processedMessage->metadata['session_id'] ?? null,
        );

        try {
            $response = $this->router->routeWithFallback($request);
        } catch (\Throwable $e) {
            Log::error('MCP Server: All providers failed', [
                'message_id' => $processedMessage->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $latency = (microtime(true) - $start) * 1000;

        $this->tokenObserver->track(
            provider: $response->provider,
            model: $response->model,
            inputTokens: $response->inputTokens,
            outputTokens: $response->outputTokens,
            cost: $response->cost,
            latencyMs: $latency,
            sessionId: $request->sessionId ?? 'unknown',
            action: 'mcp.handle',
        );

        $this->costAnalyzer->record($response);

        return $this->processMiddleware('outgoing', $response);
    }

    public function broadcast(MCPMessageDTO $message): array
    {
        $responses = [];

        foreach ($this->router->getAvailableProviders() as $name => $provider) {
            try {
                $request = new AIRequestDTO(
                    prompt: $message->content,
                    provider: $name,
                    model: $this->resolveModelForProvider($message, $name),
                    messages: [[
                        'role' => $message->role,
                        'content' => $message->content,
                    ]],
                );

                $responses[$name] = $provider->send($request);
            } catch (\Throwable $e) {
                $responses[$name] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    public function registerProvider(string $name, AIProviderInterface $provider): void
    {
        $this->router->register($name, $provider);
    }

    public function getProvider(string $name): ?AIProviderInterface
    {
        return $this->router->getProviders()[$name] ?? null;
    }

    public function getAvailableProviders(): array
    {
        return $this->router->getAvailableProviders();
    }

    public function status(): array
    {
        $providers = [];
        foreach ($this->router->getProviders() as $name => $provider) {
            $providers[$name] = [
                'available' => $provider->isAvailable(),
                'models' => $provider->models(),
            ];
        }

        return [
            'providers' => $providers,
            'token_usage' => $this->tokenObserver->summary(),
            'costs' => $this->costAnalyzer->summary(),
            'uptime' => $this->health(),
        ];
    }

    public function health(): array
    {
        return [
            'status' => 'operational',
            'timestamp' => now()->toIso8601String(),
            'providers_count' => count($this->router->getProviders()),
            'available_providers' => count($this->router->getAvailableProviders()),
        ];
    }

    public function middleware(callable $handler, string $position = 'incoming'): void
    {
        $this->middleware[$position][] = $handler;
    }

    private function processMiddleware(string $position, mixed $payload): mixed
    {
        $chain = $this->middleware[$position] ?? [];

        foreach ($chain as $handler) {
            $payload = $handler($payload);
        }

        return $payload;
    }

    private function resolveProvider(MCPMessageDTO $message): string
    {
        return $message->metadata['provider']
            ?? config('mcp.default_provider', 'openai');
    }

    private function resolveModel(MCPMessageDTO $message): string
    {
        return $message->metadata['model']
            ?? config("mcp.providers.{$this->resolveProvider($message)}.model", 'gpt-4o');
    }

    private function resolveModelForProvider(MCPMessageDTO $message, string $provider): string
    {
        return $message->metadata['models'][$provider]
            ?? config("mcp.providers.{$provider}.model", 'gpt-4o');
    }
}
