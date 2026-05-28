<?php

namespace App\Interfaces;

interface MemoryInterface
{
    public function store(string $key, mixed $value, array $metadata = []): void;

    public function retrieve(string $key): mixed;

    public function search(string $query, int $limit = 5): array;

    public function forget(string $key): bool;

    public function clear(): bool;

    public function context(): array;
}
