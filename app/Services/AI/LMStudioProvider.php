<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LMStudioProvider implements AIProviderInterface
{
    private string $baseUrl;

    private array $models;

    public function __construct()
    {
        $this->baseUrl = config('mcp.providers.lm_studio.base_url', 'http://localhost:1234');
        $this->models = ['local-model'];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $response = Http::timeout(120)->post("{$this->baseUrl}/v1/chat/completions", [
            'model' => $request->model,
            'messages' => $this->buildMessages($request),
            'temperature' => $request->temperature,
            'max_tokens' => $request->maxTokens,
            'stream' => false,
        ]);

        if ($response->failed()) {
            Log::error('LM Studio API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("LM Studio API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        $inputTokens = $data['usage']['prompt_tokens'] ?? 0;
        $outputTokens = $data['usage']['completion_tokens'] ?? 0;

        return new AIResponseDTO(
            content: $data['choices'][0]['message']['content'] ?? '',
            provider: 'lm_studio',
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
        $response = Http::timeout(120)->withOptions(['stream' => true])
            ->post("{$this->baseUrl}/v1/chat/completions", [
                'model' => $request->model,
                'messages' => $this->buildMessages($request),
                'temperature' => $request->temperature,
                'max_tokens' => $request->maxTokens,
                'stream' => true,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException("LM Studio stream error: {$response->status()}");
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
        return 'lm_studio';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/v1/models");
            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function models(): array
    {
        return $this->models;
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        return 0.0;
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
}
