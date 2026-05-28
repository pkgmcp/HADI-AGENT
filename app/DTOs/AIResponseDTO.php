<?php

namespace App\DTOs;

readonly class AIResponseDTO
{
    public function __construct(
        public string $content,
        public string $provider,
        public string $model,
        public int $inputTokens,
        public int $outputTokens,
        public float $cost,
        public float $latencyMs,
        public array $raw = [],
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
            provider: $data['provider'],
            model: $data['model'],
            inputTokens: $data['input_tokens'],
            outputTokens: $data['output_tokens'],
            cost: $data['cost'],
            latencyMs: $data['latency_ms'],
            raw: $data['raw'] ?? [],
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'cost' => $this->cost,
            'latency_ms' => $this->latencyMs,
            'raw' => $this->raw,
        ];
    }

    public function totalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }
}
