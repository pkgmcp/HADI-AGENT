<?php

namespace App\DTOs;

readonly class WorkflowDTO
{
    public function __construct(
        public string $name,
        public array $steps,
        public array $context = [],
        public string $status = 'pending',
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            steps: $data['steps'],
            context: $data['context'] ?? [],
            status: $data['status'] ?? 'pending',
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'steps' => $this->steps,
            'context' => $this->context,
            'status' => $this->status,
        ];
    }
}
