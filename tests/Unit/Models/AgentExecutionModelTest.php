<?php

use App\Models\AI\AgentExecution;

it('has fillable attributes', function () {
    $model = new AgentExecution;

    expect($model->getFillable())->toContain('agent_name');
    expect($model->getFillable())->toContain('status');
    expect($model->getFillable())->toContain('session_id');
});

it('casts attributes correctly', function () {
    $model = new AgentExecution;

    expect($model->getCasts()['task'])->toBe('array');
    expect($model->getCasts()['response'])->toBe('array');
    expect($model->getCasts()['metadata'])->toBe('array');
    expect($model->getCasts()['input_tokens'])->toBe('integer');
    expect($model->getCasts()['cost'])->toBe('float');
});

it('scopes by agent name', function () {
    AgentExecution::factory()->create(['agent_name' => 'PlannerAgent']);
    AgentExecution::factory()->create(['agent_name' => 'CoderAgent']);

    expect(AgentExecution::byAgent('PlannerAgent')->count())->toBe(1);
});

it('scopes successful executions', function () {
    AgentExecution::factory()->create(['status' => 'completed']);
    AgentExecution::factory()->create(['status' => 'failed']);

    expect(AgentExecution::successful()->count())->toBe(1);
});

it('scopes failed executions', function () {
    AgentExecution::factory()->create(['status' => 'completed']);
    AgentExecution::factory()->create(['status' => 'failed']);

    expect(AgentExecution::failed()->count())->toBe(1);
});

it('creates execution with valid data', function () {
    $exec = AgentExecution::create([
        'agent_name' => 'DebuggerAgent',
        'agent_type' => 'debugger',
        'session_id' => 'create_session',
        'task' => ['type' => 'diagnose', 'code' => 'test'],
        'status' => 'pending',
    ]);

    expect($exec->exists)->toBeTrue();
    expect($exec->agent_name)->toBe('DebuggerAgent');
    expect($exec->status)->toBe('pending');
});

it('stores task as array', function () {
    $task = ['file' => 'test.php', 'action' => 'review'];

    $exec = AgentExecution::create([
        'agent_name' => 'ReviewAgent',
        'agent_type' => 'reviewer',
        'session_id' => 'task_test',
        'task' => $task,
        'status' => 'pending',
    ]);

    expect($exec->task)->toBe($task);
});
