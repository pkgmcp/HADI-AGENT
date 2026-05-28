<?php

namespace App\Interfaces;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\DTOs\MCPMessageDTO;

interface MCPServerInterface
{
    public function handle(MCPMessageDTO $message): AIResponseDTO;

    public function broadcast(MCPMessageDTO $message): array;

    public function registerProvider(string $name, AIProviderInterface $provider): void;

    public function getProvider(string $name): ?AIProviderInterface;

    public function getAvailableProviders(): array;

    public function status(): array;
}
