<?php

use App\Http\Controllers\AgentController;
use App\Services\Agents\PlannerAgent;

it('registers and lists agents', function () {
    $controller = app(AgentController::class);
    $controller->register('planner', app(PlannerAgent::class));

    $response = $controller->list();

    expect($response->getData()->data)->toHaveCount(1);
    expect($response->getData()->data[0]->name)->toBe('PlannerAgent');
});

it('returns 404 for unknown agent', function () {
    $response = $this->postJson('/api/v1/agents/unknown/execute', [
        'task' => 'test',
    ]);

    $response->assertStatus(404);
});

it('requires task for agent execution', function () {
    $response = $this->postJson('/api/v1/agents/planner/execute', []);

    $response->assertStatus(422);
});

it('lists all registered agents', function () {
    AgentController::macro('setAgents', function ($agents) {
        $this->agents = $agents;
    });

    $response = $this->getJson('/api/v1/agents');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});
