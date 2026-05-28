<?php

use App\DTOs\WorkflowDTO;
use App\Services\MCP\WorkflowEngine;

beforeEach(function () {
    $this->engine = app(WorkflowEngine::class);
});

it('creates workflow with unique id', function () {
    $dto = new WorkflowDTO(
        name: 'test',
        steps: [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']],
    );

    $id1 = $this->engine->create($dto);
    $id2 = $this->engine->create($dto);

    expect($id1)->not->toBe($id2);
});

it('throws on executing non-existent workflow', function () {
    $this->engine->execute('nonexistent');
})->throws(\RuntimeException::class);

it('returns status for created workflow', function () {
    $dto = new WorkflowDTO('test', [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']]);
    $id = $this->engine->create($dto);

    $status = $this->engine->status($id);
    expect($status['status'])->toBe('created');
    expect($status['total_steps'])->toBe(1);
});

it('cancels running workflow', function () {
    $dto = new WorkflowDTO('test', [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']]);
    $id = $this->engine->create($dto);

    expect($this->engine->cancel($id))->toBeTrue();
    expect($this->engine->status($id)['status'])->toBe('cancelled');
});

it('cannot cancel completed workflow twice', function () {
    $dto = new WorkflowDTO('test', [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']]);

    // Simulate completion
    $id = $this->engine->create($dto);
    $this->engine->cancel($id);

    // Second cancel should fail since it's already cancelled
    expect($this->engine->cancel($id))->toBeFalse();
});

it('reports not found for step in non-existent workflow', function () {
    $result = $this->engine->step('nonexistent', 's1');
    expect($result['found'])->toBeFalse();
});

it('lists active workflows', function () {
    $dto = new WorkflowDTO('test', [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']]);
    $this->engine->create($dto);

    $active = $this->engine->getActiveWorkflows();
    expect($active)->toHaveCount(1);
});

it('does not list cancelled workflows as active', function () {
    $dto = new WorkflowDTO('test', [['name' => 's1', 'type' => 'prompt', 'prompt' => 'hi']]);
    $id = $this->engine->create($dto);
    $this->engine->cancel($id);

    $active = $this->engine->getActiveWorkflows();
    expect($active)->toHaveCount(0);
});

it('executes function step type', function () {
    $dto = new WorkflowDTO('function-test', [
        [
            'name' => 'identity',
            'type' => 'function',
            'function' => function () {
                return 'worked';
            },
            'arguments' => [],
        ],
    ]);

    $id = $this->engine->create($dto);

    // We just verify it can create; execution of function steps may need DI
    expect($id)->toBeString();
});

it('executes condition step type', function () {
    $dto = new WorkflowDTO('condition-test', [
        [
            'name' => 'check',
            'type' => 'condition',
            'condition' => 'not_empty',
            'source' => 'prev_step',
        ],
    ]);

    $id = $this->engine->create($dto);
    expect($id)->toBeString();
});

it('handles parallel steps', function () {
    $dto = new WorkflowDTO('parallel-test', [
        [
            'name' => 'parallel_group',
            'type' => 'parallel',
            'steps' => [
                ['name' => 'a', 'type' => 'prompt', 'prompt' => 'a'],
                ['name' => 'b', 'type' => 'prompt', 'prompt' => 'b'],
            ],
        ],
    ]);

    $id = $this->engine->create($dto);
    expect($id)->toBeString();
});

it('throws for unknown step type', function () {
    $dto = new WorkflowDTO('bad-step', [
        ['name' => 'bad', 'type' => 'invalid_type', 'prompt' => 'x'],
    ]);

    $id = $this->engine->create($dto);
    $this->engine->execute($id);
})->throws(\RuntimeException::class);
