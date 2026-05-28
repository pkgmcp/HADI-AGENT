<?php

namespace App\DTOs;

readonly class AIRequestDTO
{
    public function __construct(
        public string $prompt,
        public string $provider,
        public string $model,
        public array $messages = [],
        public float $temperature = 0.7,
        public int $maxTokens = 4096,
        public array $options = [],
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: $data['prompt'],
            provider: $data['provider'],
            model: $data['model'],
            messages: $data['messages'] ?? [],
            temperature: $data['temperature'] ?? 0.7,
            maxTokens: $data['max_tokens'] ?? 4096,
            options: $data['options'] ?? [],
            sessionId: $data['session_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'provider' => $this->provider,
            'model' => $this->model,
            'messages' => $this->messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'options' => $this->options,
            'session_id' => $this->sessionId,
        ];
    }
}
