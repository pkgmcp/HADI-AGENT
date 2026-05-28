<?php

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;

class MockAIProvider implements AIProviderInterface
{
    private string $name;
    private bool $available;
    private ?float $latency;

    public function __construct(string $name = 'mock', bool $available = true, ?float $latency = null)
    {
        $this->name = $name;
        $this->available = $available;
        $this->latency = $latency;
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        if ($this->latency) {
            usleep((int)($this->latency * 1000));
        }

        return new AIResponseDTO(
            content: "Mock response from {$this->name}: " . $request->prompt,
            provider: $this->name,
            model: $request->model,
            inputTokens: strlen($request->prompt),
            outputTokens: 50,
            cost: 0.001,
            latencyMs: $this->latency ?? 50,
        );
    }

    public function stream(AIRequestDTO $request): iterable
    {
        yield "chunk1 from {$this->name} ";
        yield "chunk2 from {$this->name}";
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function models(): array
    {
        return ['mock-model-1', 'mock-model-2'];
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        return 0.001;
    }
}

beforeEach(function () {
    $this->mockProvider = new MockAIProvider('test_mock');
});

it('mock provider returns expected name', function () {
    expect($this->mockProvider->name())->toBe('test_mock');
});

it('mock provider is available by default', function () {
    expect($this->mockProvider->isAvailable())->toBeTrue();
});

it('mock provider can be set unavailable', function () {
    $unavailable = new MockAIProvider('down', false);
    expect($unavailable->isAvailable())->toBeFalse();
});

it('mock provider sends and returns response', function () {
    $request = new AIRequestDTO(
        prompt: 'Hello mock',
        provider: 'test_mock',
        model: 'mock-model-1',
    );

    $response = $this->mockProvider->send($request);

    expect($response)->toBeInstanceOf(AIResponseDTO::class);
    expect($response->content)->toContain('Hello mock');
    expect($response->provider)->toBe('test_mock');
});

it('mock provider streams content', function () {
    $request = new AIRequestDTO('Stream test', 'mock', 'm1');
    $chunks = iterator_to_array($this->mockProvider->stream($request));

    expect($chunks)->toHaveCount(2);
    expect(implode('', $chunks))->toContain('chunk1');
});

it('mock provider returns models list', function () {
    expect($this->mockProvider->models())->toBe(['mock-model-1', 'mock-model-2']);
});

it('mock provider calculates fixed cost', function () {
    expect($this->mockProvider->calculateCost(100, 50))->toBe(0.001);
});

it('can register mock provider with MCPServer and route', function () {
    $server = app(\App\Services\MCP\MCPServer::class);
    $server->registerProvider('mock_integration', $this->mockProvider);

    $providers = $server->getAvailableProviders();
    expect($providers)->toHaveKey('mock_integration');
});
