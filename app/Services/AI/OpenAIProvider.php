<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    private string $apiKey;

    private string $endpoint;

    private array $models;

    public function __construct()
    {
        $this->apiKey = config('mcp.providers.openai.api_key');
        $this->endpoint = config('mcp.providers.openai.endpoint', 'https://api.openai.com/v1');
        $this->models = [
            'gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo',
        ];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $messages = $this->buildMessages($request);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->endpoint}/chat/completions", [
            'model' => $request->model,
            'messages' => $messages,
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("OpenAI API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        $inputTokens = $data['usage']['prompt_tokens'] ?? 0;
        $outputTokens = $data['usage']['completion_tokens'] ?? 0;
        $content = $data['choices'][0]['message']['content'] ?? '';

        return new AIResponseDTO(
            content: $content,
            provider: 'openai',
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
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->withOptions(['stream' => true])->post("{$this->endpoint}/chat/completions", [
            'model' => $request->model,
            'messages' => $messages,
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
            'stream' => true,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("OpenAI stream error: {$response->status()}");
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
        return 'openai';
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
            'gpt-4o' => ['input' => 0.00001, 'output' => 0.00003],
            'gpt-4o-mini' => ['input' => 0.0000015, 'output' => 0.000006],
            'gpt-4-turbo' => ['input' => 0.00001, 'output' => 0.00003],
            'gpt-4' => ['input' => 0.00003, 'output' => 0.00006],
            'gpt-3.5-turbo' => ['input' => 0.000001, 'output' => 0.000002],
        ];

        $model = $this->getBaseModel();
        $rate = $rates[$model] ?? $rates['gpt-4o'];

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
        $model = config('mcp.providers.openai.model', 'gpt-4o');

        foreach (['gpt-4o', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'] as $base) {
            if (str_starts_with($model, $base)) {
                return $base;
            }
        }

        return 'gpt-4o';
    }
}
