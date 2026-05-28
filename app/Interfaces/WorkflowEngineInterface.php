<?php

namespace App\Interfaces;

use App\DTOs\WorkflowDTO;

interface WorkflowEngineInterface
{
    public function create(WorkflowDTO $workflow): string;

    public function execute(string $workflowId): array;

    public function step(string $workflowId, string $stepName): array;

    public function status(string $workflowId): array;

    public function cancel(string $workflowId): bool;

    public function retry(string $workflowId): array;
}
