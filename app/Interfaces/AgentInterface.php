<?php

namespace App\Interfaces;

use App\DTOs\AgentDTO;
use App\DTOs\AIResponseDTO;

interface AgentInterface
{
    public function execute(string $task, array $context = []): AIResponseDTO;

    public function getName(): string;

    public function getType(): string;

    public function getConfig(): AgentDTO;

    public function canHandle(string $task): bool;

    public function reset(): void;
}
