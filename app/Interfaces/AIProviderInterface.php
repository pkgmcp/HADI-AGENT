<?php

namespace App\Interfaces;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;

interface AIProviderInterface
{
    public function send(AIRequestDTO $request): AIResponseDTO;

    public function stream(AIRequestDTO $request): iterable;

    public function name(): string;

    public function isAvailable(): bool;

    public function models(): array;

    public function calculateCost(int $inputTokens, int $outputTokens): float;
}
