<?php

use App\Services\MCP\WorkflowEngine;

beforeEach(function () {
    $this->workflowEngine = app(WorkflowEngine::class);
});

it('creates a workflow', function () {
    $dto = new \App\DTOs\WorkflowDTO(
        name: 'Test Workflow',
        steps: [
            ['name' => 'step1', 'type' => 'prompt', 'prompt' => 'Hello'],
        ],
    );

    $id = $this->workflowEngine->create($dto);

    expect($id)->toBeString();
    expect(str_starts_with($id, 'wf_'))->toBeTrue();
});

it('checks workflow status', function () {
    $dto = new \App\DTOs\WorkflowDTO(
        name: 'Status Test',
        steps: [
            ['name' => 'step1', 'type' => 'prompt', 'prompt' => 'Test'],
        ],
    );

    $id = $this->workflowEngine->create($dto);
    $status = $this->workflowEngine->status($id);

    expect($status['found'])->toBeTrue();
    expect($status['status'])->toBe('created');
});

it('returns not found for unknown workflow', function () {
    $status = $this->workflowEngine->status('nonexistent');

    expect($status['found'])->toBeFalse();
});

it('cancels a running workflow', function () {
    $dto = new \App\DTOs\WorkflowDTO(
        name: 'Cancel Test',
        steps: [
            ['name' => 'step1', 'type' => 'prompt', 'prompt' => 'Test'],
        ],
    );

    $id = $this->workflowEngine->create($dto);
    $cancelled = $this->workflowEngine->cancel($id);

    expect($cancelled)->toBeTrue();

    $status = $this->workflowEngine->status($id);
    expect($status['status'])->toBe('cancelled');
});

it('validates workflow creation request', function () {
    $response = $this->postJson('/api/v1/workflows', [
        'name' => '',
        'steps' => [],
    ]);

    $response->assertStatus(422);
});
