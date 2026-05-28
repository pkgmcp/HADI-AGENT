<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    private string $apiKey;

    private string $endpoint;

    private array $models;

    public function __construct()
    {
        $this->apiKey = config('mcp.providers.gemini.api_key');
        $this->endpoint = config('mcp.providers.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1');
        $this->models = ['gemini-pro', 'gemini-pro-vision'];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $contents = $this->buildContents($request);

        $response = Http::timeout(120)->post(
            "{$this->endpoint}/models/{$request->model}:generateContent?key={$this->apiKey}",
            ['contents' => $contents]
        );

        if ($response->failed()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("Gemini API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $inputTokens = 0;
        $outputTokens = 0;

        if (isset($data['usageMetadata'])) {
            $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? 0;
            $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? 0;
        }

        return new AIResponseDTO(
            content: $content,
            provider: 'gemini',
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
        $contents = $this->buildContents($request);

        $response = Http::timeout(120)->withOptions(['stream' => true])->post(
            "{$this->endpoint}/models/{$request->model}:streamGenerateContent?key={$this->apiKey}",
            ['contents' => $contents]
        );

        if ($response->failed()) {
            throw new \RuntimeException("Gemini stream error: {$response->status()}");
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $body->read(1024);
            $chunks = json_decode($line, true);
            if ($chunks && isset($chunks['candidates'][0]['content']['parts'][0]['text'])) {
                yield $chunks['candidates'][0]['content']['parts'][0]['text'];
            }
        }
    }

    public function name(): string
    {
        return 'gemini';
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
        return 0.0;
    }

    private function buildContents(AIRequestDTO $request): array
    {
        if (!empty($request->messages)) {
            $contents = [];
            foreach ($request->messages as $msg) {
                $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content'] ?? '']],
                ];
            }
            return $contents;
        }

        return [
            [
                'role' => 'user',
                'parts' => [['text' => $request->prompt]],
            ],
        ];
    }
}
