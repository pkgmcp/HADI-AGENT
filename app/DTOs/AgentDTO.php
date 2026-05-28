<?php

namespace App\DTOs;

readonly class AgentDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public string $provider,
        public string $model,
        public float $temperature,
        public int $maxIterations,
        public array $config = [],
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            provider: $data['provider'],
            model: $data['model'],
            temperature: $data['temperature'] ?? 0.7,
            maxIterations: $data['max_iterations'] ?? 3,
            config: $data['config'] ?? [],
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'provider' => $this->provider,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_iterations' => $this->maxIterations,
            'config' => $this->config,
        ];
    }
}
