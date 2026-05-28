<?php

namespace App\DTOs;

readonly class MCPMessageDTO
{
    public function __construct(
        public string $role,
        public string $content,
        public array $metadata = [],
        public ?int $tokenCount = null,
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            role: $data['role'],
            content: $data['content'],
            metadata: $data['metadata'] ?? [],
            tokenCount: $data['token_count'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'content' => $this->content,
            'metadata' => $this->metadata,
            'token_count' => $this->tokenCount,
        ];
    }
}
