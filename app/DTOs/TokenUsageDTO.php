<?php

namespace App\DTOs;

readonly class TokenUsageDTO
{
    public function __construct(
        public string $provider,
        public string $model,
        public int $inputTokens,
        public int $outputTokens,
        public float $cost,
        public float $latencyMs,
        public string $sessionId,
        public string $action,
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            provider: $data['provider'],
            model: $data['model'],
            inputTokens: $data['input_tokens'],
            outputTokens: $data['output_tokens'],
            cost: $data['cost'],
            latencyMs: $data['latency_ms'],
            sessionId: $data['session_id'],
            action: $data['action'],
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'cost' => $this->cost,
            'latency_ms' => $this->latencyMs,
            'session_id' => $this->sessionId,
            'action' => $this->action,
        ];
    }

    public function totalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }
}
