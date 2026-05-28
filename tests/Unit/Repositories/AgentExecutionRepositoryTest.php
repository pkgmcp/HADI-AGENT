<?php

use App\Models\AI\AgentExecution;
use App\Repositories\AgentExecutionRepository;

beforeEach(function () {
    $this->repo = new AgentExecutionRepository;
});

it('creates an agent execution record', function () {
    $exec = $this->repo->create([
        'agent_name' => 'PlannerAgent',
        'agent_type' => 'planner',
        'session_id' => 'exec_session',
        'task' => ['description' => 'Test task'],
        'status' => 'pending',
    ]);

    expect($exec)->toBeInstanceOf(AgentExecution::class);
    expect($exec->agent_name)->toBe('PlannerAgent');
});

it('updates execution status', function () {
    $exec = $this->repo->create([
        'agent_name' => 'CoderAgent',
        'agent_type' => 'coder',
        'session_id' => 'upd_exec',
        'status' => 'running',
    ]);

    $updated = $this->repo->updateStatus($exec->id, 'completed');
    expect($updated)->toBeTrue();

    $fresh = AgentExecution::find($exec->id);
    expect($fresh->status)->toBe('completed');
});

it('updates execution with error', function () {
    $exec = $this->repo->create([
        'agent_name' => 'TestAgent',
        'agent_type' => 'test',
        'session_id' => 'err_exec',
        'status' => 'running',
    ]);

    $this->repo->updateStatus($exec->id, 'failed', 'Something went wrong');

    $fresh = AgentExecution::find($exec->id);
    expect($fresh->status)->toBe('failed');
    expect($fresh->error)->toBe('Something went wrong');
});

it('finds executions by session', function () {
    $this->repo->create([
        'agent_name' => 'A1', 'agent_type' => 't',
        'session_id' => 'find_sess', 'status' => 'pending',
    ]);

    $this->repo->create([
        'agent_name' => 'A2', 'agent_type' => 't',
        'session_id' => 'find_sess', 'status' => 'pending',
    ]);

    $executions = $this->repo->findBySession('find_sess');
    expect($executions)->toHaveCount(2);
});

it('returns empty for session with no executions', function () {
    $executions = $this->repo->findBySession('no_execs');
    expect($executions)->toHaveCount(0);
});

it('gets agent stats', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->repo->create([
            'agent_name' => 'StatsAgent',
            'agent_type' => 'stats',
            'session_id' => 'stats_sess',
            'status' => 'completed',
            'latency_ms' => 100,
            'cost' => 0.001,
        ]);
    }

    $stats = $this->repo->getAgentStats('StatsAgent');
    expect($stats['total_executions'])->toBe(3);
    expect($stats['successful'])->toBe(3);
});

it('gets recent executions', function () {
    $this->repo->create([
        'agent_name' => 'RecentAgent', 'agent_type' => 't',
        'session_id' => 'rec_sess', 'status' => 'completed',
    ]);

    $recent = $this->repo->getRecentExecutions(5);
    expect($recent)->toHaveCount(1);
});

it('handles stats for non-existent agent', function () {
    $stats = $this->repo->getAgentStats('NonExistent');
    expect($stats['total_executions'] ?? 0)->toBe(0);
});
