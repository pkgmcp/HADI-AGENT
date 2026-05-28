<?php

use App\Services\Agents\BaseAgent;

it('abstract base agent cannot be instantiated directly', function () {
    $reflection = new ReflectionClass(BaseAgent::class);
    expect($reflection->isAbstract())->toBeTrue();
});

it('all agents extend base agent', function () {
    $agents = [
        App\Services\Agents\PlannerAgent::class,
        App\Services\Agents\CoderAgent::class,
        App\Services\Agents\ReviewAgent::class,
        App\Services\Agents\DebuggerAgent::class,
        App\Services\Agents\MemoryAgent::class,
        App\Services\Agents\SecurityAgent::class,
        App\Services\Agents\DeploymentAgent::class,
        App\Services\Agents\WorkflowAgent::class,
    ];

    foreach ($agents as $agentClass) {
        $reflection = new ReflectionClass($agentClass);
        expect($reflection->isSubclassOf(BaseAgent::class))->toBeTrue();
    }
});

it('all agents implement canHandle method', function () {
    $agents = [
        App\Services\Agents\PlannerAgent::class,
        App\Services\Agents\CoderAgent::class,
        App\Services\Agents\ReviewAgent::class,
        App\Services\Agents\DebuggerAgent::class,
        App\Services\Agents\MemoryAgent::class,
        App\Services\Agents\SecurityAgent::class,
        App\Services\Agents\DeploymentAgent::class,
        App\Services\Agents\WorkflowAgent::class,
    ];

    foreach ($agents as $agentClass) {
        $agent = app($agentClass);
        expect($agent->canHandle('test'))->toBeBool();
    }
});

it('all agents have required methods', function () {
    $agents = [
        App\Services\Agents\PlannerAgent::class,
        App\Services\Agents\CoderAgent::class,
        App\Services\Agents\ReviewAgent::class,
        App\Services\Agents\DebuggerAgent::class,
        App\Services\Agents\MemoryAgent::class,
        App\Services\Agents\SecurityAgent::class,
        App\Services\Agents\DeploymentAgent::class,
        App\Services\Agents\WorkflowAgent::class,
    ];

    foreach ($agents as $agentClass) {
        $agent = app($agentClass);

        expect($agent->getName())->toBeString();
        expect($agent->getType())->toBeString();
        expect($agent->getConfig())->toBeInstanceOf(App\DTOs\AgentDTO::class);

        $agent->reset();
        expect($agent->getHistory())->toBe([]);
    }
});
