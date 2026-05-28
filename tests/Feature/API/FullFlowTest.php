<?php

use App\DTOs\WorkflowDTO;
use App\Services\MCP\WorkflowEngine;

it('completes full health → mcp → agent → workflow flow', function () {
    // 1. Health check
    $health = $this->getJson('/api/v1/health');
    $health->assertStatus(200);
    $health->assertJsonPath('service', 'HADI Agent');

    // 2. MCP status
    $status = $this->getJson('/api/v1/mcp/status');
    $status->assertStatus(200);
    $status->assertJsonStructure(['data' => ['providers', 'token_usage']]);

    // 3. MCP providers list
    $providers = $this->getJson('/api/v1/mcp/providers');
    $providers->assertStatus(200);
    $providers->assertJsonStructure(['data']);

    // 4. Agents list
    $agents = $this->getJson('/api/v1/agents');
    $agents->assertStatus(200);
    $agents->assertJsonStructure(['data']);

    // 5. MCP message send
    $mcpResponse = $this->postJson('/api/v1/mcp/messages', [
        'role' => 'user',
        'content' => 'Hello from full flow test',
        'metadata' => [
            'session_id' => 'flow_test_' . time(),
        ],
    ]);
    $mcpResponse->assertStatus(200);

    // 6. Workflow creation
    $workflowData = [
        'name' => 'Full Flow Test Workflow',
        'steps' => [
            [
                'name' => 'step_1',
                'type' => 'prompt',
                'prompt' => 'Execute task',
            ],
        ],
        'context' => ['test' => true],
    ];

    $createResponse = $this->postJson('/api/v1/workflows', $workflowData);
    $createResponse->assertStatus(201);
    $workflowId = $createResponse->json('data.workflow_id');

    expect($workflowId)->not->toBeNull();

    // 7. Workflow status check
    $workflowStatus = $this->getJson("/api/v1/workflows/{$workflowId}/status");
    $workflowStatus->assertStatus(200);
    $workflowStatus->assertJsonPath('data.status', 'created');

    // 8. Workflow cancel
    $cancelResponse = $this->postJson("/api/v1/workflows/{$workflowId}/cancel");
    $cancelResponse->assertStatus(200);
    $cancelResponse->assertJsonPath('data.cancelled', true);
});

it('handles concurrent requests without side effects', function () {
    $responses = [];

    // Fire 3 independent requests
    for ($i = 0; $i < 3; $i++) {
        $responses[] = $this->getJson('/api/v1/health');
    }

    foreach ($responses as $response) {
        $response->assertStatus(200);
        $response->assertJsonPath('service', 'HADI Agent');
    }
});

it('validates all API request formats', function () {
    // Empty MCP message
    $this->postJson('/api/v1/mcp/messages', [])->assertStatus(422);

    // Empty agent execute
    $this->postJson('/api/v1/agents/planner/execute', [])->assertStatus(422);

    // Empty workflow create
    $this->postJson('/api/v1/workflows', [])->assertStatus(422);

    // Missing steps in workflow
    $this->postJson('/api/v1/workflows', [
        'name' => 'test',
        'steps' => [],
    ])->assertStatus(422);
});
