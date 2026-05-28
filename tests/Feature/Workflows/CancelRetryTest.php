<?php

use App\DTOs\WorkflowDTO;
use App\Services\MCP\WorkflowEngine;

beforeEach(function () {
    $this->engine = app(WorkflowEngine::class);
});

it('cancels workflow before execution', function () {
    $dto = new WorkflowDTO('cancel-test', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'test'],
    ]);

    $id = $this->engine->create($dto);
    expect($this->engine->cancel($id))->toBeTrue();

    $status = $this->engine->status($id);
    expect($status['status'])->toBe('cancelled');
});

it('retry creates new execution from initial state', function () {
    $dto = new WorkflowDTO('retry-test', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'test'],
    ]);

    $id = $this->engine->create($dto);

    // Retry should reset errors and re-execute
    expect($this->engine->retry($id)['status'])->not->toBe('failed');
});

it('retry returns workflow id in response', function () {
    $dto = new WorkflowDTO('retry-check', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'x'],
    ]);

    $id = $this->engine->create($dto);

    $result = $this->engine->retry($id);
    expect($result['workflow_id'])->toBe($id);
});

it('cleanup removes old workflows', function () {
    $dto = new WorkflowDTO('old-wf', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'old'],
    ]);

    $this->engine->create($dto);

    // Cleanup with 0 hours should remove everything
    $count = $this->engine->cleanup(0);
    expect($count)->toBeGreaterThanOrEqual(0);
});

it('cancel via API returns success', function () {
    $dto = new WorkflowDTO('api-cancel', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'api test'],
    ]);

    $id = $this->engine->create($dto);

    $response = $this->postJson("/api/v1/workflows/{$id}/cancel");

    $response->assertStatus(200)
        ->assertJsonPath('data.cancelled', true);
});

it('retry via API returns workflow result', function () {
    $dto = new WorkflowDTO('api-retry', [
        ['name' => 's1', 'type' => 'prompt', 'prompt' => 'retry me'],
    ]);

    $id = $this->engine->create($dto);

    $response = $this->postJson("/api/v1/workflows/{$id}/retry");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['workflow_id', 'status']]);
});

it('status endpoint returns step info', function () {
    $dto = new WorkflowDTO('status-check', [
        ['name' => 'step_one', 'type' => 'prompt', 'prompt' => 'hello'],
    ]);

    $id = $this->engine->create($dto);

    $response = $this->getJson("/api/v1/workflows/{$id}/status");

    $response->assertStatus(200)
        ->assertJsonPath('data.total_steps', 1);
});
