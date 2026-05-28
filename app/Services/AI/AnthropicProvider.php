<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicProvider implements AIProviderInterface
{
    private string $apiKey;

    private string $endpoint;

    private array $models;

    public function __construct()
    {
        $this->apiKey = config('mcp.providers.anthropic.api_key');
        $this->endpoint = config('mcp.providers.anthropic.endpoint', 'https://api.anthropic.com/v1');
        $this->models = [
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
            'claude-2.1',
            'claude-2.0',
        ];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $messages = $this->buildMessages($request);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->endpoint}/messages", [
            'model' => $request->model,
            'messages' => $messages,
            'max_tokens' => $request->maxTokens,
            'temperature' => $request->temperature,
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("Anthropic API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        $inputTokens = $data['usage']['input_tokens'] ?? 0;
        $outputTokens = $data['usage']['output_tokens'] ?? 0;
        $content = '';

        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $content .= $block['text'] ?? '';
            }
        }

        return new AIResponseDTO(
            content: $content,
            provider: 'anthropic',
            model: $request->model,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            cost: $this->calculateCost($inputTokens, $outputTokens),
            latencyMs: $latency,
            raw: $data,
        );
    }

    public function stream(AIRequestDTO $request): iterable
    {
        $messages = $this->buildMessages($request);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->withOptions(['stream' => true])->post("{$this->endpoint}/messages", [
            'model' => $request->model,
            'messages' => $messages,
            'max_tokens' => $request->maxTokens,
            'temperature' => $request->temperature,
            'stream' => true,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Anthropic stream error: {$response->status()}");
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $body->read(1024);
            if ($line && str_starts_with($line, 'data: ')) {
                $data = json_decode(substr($line, 6), true);
                if ($data && $data['type'] === 'content_block_delta') {
                    yield $data['delta']['text'] ?? '';
                }
            }
        }
    }

    public function name(): string
    {
        return 'anthropic';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function models(): array
    {
        return $this->models;
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        $rates = [
            'claude-3-opus-20240229' => ['input' => 0.000015, 'output' => 0.000075],
            'claude-3-sonnet-20240229' => ['input' => 0.000003, 'output' => 0.000015],
            'claude-3-haiku-20240307' => ['input' => 0.00000025, 'output' => 0.00000125],
            'claude-2.1' => ['input' => 0.000008, 'output' => 0.000024],
            'claude-2.0' => ['input' => 0.000008, 'output' => 0.000024],
        ];

        $model = $this->getBaseModel();
        $rate = $rates[$model] ?? $rates['claude-3-sonnet-20240229'];

        return ($inputTokens * $rate['input']) + ($outputTokens * $rate['output']);
    }

    private function buildMessages(AIRequestDTO $request): array
    {
        if (!empty($request->messages)) {
            return array_filter($request->messages, fn($m) => ($m['role'] ?? '') !== 'system');
        }

        return [
            ['role' => 'user', 'content' => $request->prompt],
        ];
    }

    private function getBaseModel(): string
    {
        $model = config('mcp.providers.anthropic.model', 'claude-3-sonnet-20240229');

        foreach (['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku', 'claude-2'] as $base) {
            if (str_starts_with($model, $base)) {
                $baseMap = [
                    'claude-3-opus' => 'claude-3-opus-20240229',
                    'claude-3-sonnet' => 'claude-3-sonnet-20240229',
                    'claude-3-haiku' => 'claude-3-haiku-20240307',
                    'claude-2' => 'claude-2.1',
                ];
                return $baseMap[$base];
            }
        }

        return 'claude-3-sonnet-20240229';
    }
}
