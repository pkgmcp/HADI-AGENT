<?php

namespace App\Services\AI;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\Interfaces\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AIProviderInterface
{
    private string $baseUrl;

    private array $models;

    public function __construct()
    {
        $this->baseUrl = config('mcp.providers.ollama.base_url', 'http://localhost:11434');
        $this->models = [];
    }

    public function send(AIRequestDTO $request): AIResponseDTO
    {
        $start = microtime(true);

        $response = Http::timeout(120)->post("{$this->baseUrl}/api/chat", [
            'model' => $request->model,
            'messages' => $this->buildMessages($request),
            'stream' => false,
            'options' => [
                'temperature' => $request->temperature,
                'num_predict' => $request->maxTokens,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Ollama API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("Ollama API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        $latency = (microtime(true) - $start) * 1000;

        return new AIResponseDTO(
            content: $data['message']['content'] ?? '',
            provider: 'ollama',
            model: $request->model,
            inputTokens: $data['prompt_eval_count'] ?? 0,
            outputTokens: $data['eval_count'] ?? 0,
            cost: $this->calculateCost($data['prompt_eval_count'] ?? 0, $data['eval_count'] ?? 0),
            latencyMs: $latency,
            raw: $data,
        );
    }

    public function stream(AIRequestDTO $request): iterable
    {
        $response = Http::timeout(120)->withOptions(['stream' => true])
            ->post("{$this->baseUrl}/api/chat", [
                'model' => $request->model,
                'messages' => $this->buildMessages($request),
                'stream' => true,
                'options' => [
                    'temperature' => $request->temperature,
                    'num_predict' => $request->maxTokens,
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException("Ollama stream error: {$response->status()}");
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $line = $body->read(1024);
            $lines = explode("\n", trim($line));

            foreach ($lines as $jsonLine) {
                if (empty($jsonLine)) continue;
                $data = json_decode($jsonLine, true);
                if ($data && isset($data['message']['content'])) {
                    yield $data['message']['content'];
                }
            }
        }
    }

    public function name(): string
    {
        return 'ollama';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/api/tags");
            if ($response->successful()) {
                $this->models = collect($response->json()['models'] ?? [])
                    ->pluck('name')
                    ->toArray();
                return true;
            }
            return false;
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

    public function pullModel(string $model): array
    {
        $response = Http::timeout(300)->post("{$this->baseUrl}/api/pull", [
            'model' => $model,
            'stream' => false,
        ]);

        return $response->json();
    }

    public function listModels(): array
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");

        if ($response->failed()) {
            return [];
        }

        return $response->json()['models'] ?? [];
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
