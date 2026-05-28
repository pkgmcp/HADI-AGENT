<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekProvider implements AIProviderInterface
{
    private string $apiKey;

    private string $endpoint;

    private array $models;

    public function __construct()
    {
        $this->apiKey = config('mcp.providers.deepseek.api_key');
        $this->endpoint = config('mcp.providers.deepseek.endpoint', 'https://api.deepseek.com/v1');
        $this->models = ['deepseek-chat', 'deepseek-coder'];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->endpoint}/chat/completions", [
            'model' => $request->model,
            'messages' => $this->buildMessages($request),
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
        ]);

        if ($response->failed()) {
            Log::error('DeepSeek API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("DeepSeek API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        $inputTokens = $data['usage']['prompt_tokens'] ?? 0;
        $outputTokens = $data['usage']['completion_tokens'] ?? 0;

        return new AIResponseDTO(
            content: $data['choices'][0]['message']['content'] ?? '',
            provider: 'deepseek',
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
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->withOptions(['stream' => true])->post("{$this->endpoint}/chat/completions", [
            'model' => $request->model,
            'messages' => $this->buildMessages($request),
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
            'stream' => true,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("DeepSeek stream error: {$response->status()}");
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $body->read(1024);
            if ($line && str_starts_with($line, 'data: ')) {
                $data = json_decode(substr($line, 6), true);
                if ($data && isset($data['choices'][0]['delta']['content'])) {
                    yield $data['choices'][0]['delta']['content'];
                }
            }
        }
    }

    public function name(): string
    {
        return 'deepseek';
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
            'deepseek-chat' => ['input' => 0.00000014, 'output' => 0.00000028],
            'deepseek-coder' => ['input' => 0.00000014, 'output' => 0.00000028],
        ];

        $model = $this->getBaseModel();
        $rate = $rates[$model] ?? $rates['deepseek-chat'];

        return ($inputTokens * $rate['input']) + ($outputTokens * $rate['output']);
    }

    private function buildMessages(AIRequestDTO $request): array
    {
        if (!empty($request->messages)) {
            return $request->messages;
        }

        return [
            ['role' => 'user', 'content' => $request->prompt],
        ];
    }

    private function getBaseModel(): string
    {
        $model = config('mcp.providers.deepseek.model', 'deepseek-chat');

        foreach (['deepseek-chat', 'deepseek-coder'] as $base) {
            if (str_starts_with($model, $base)) {
                return $base;
            }
        }

        return 'deepseek-chat';
    }
}
