<?php

use App\DTOs\AIResponseDTO;
use App\Services\MCP\MCPServer;
use Mockery\MockInterface;

beforeEach(function () {
    $this->mockServer = Mockery::mock(MCPServer::class);
    $this->app->instance(MCPServer::class, $this->mockServer);
});

it('handles MCP message via POST', function () {
    $this->mockServer->shouldReceive('handle')->once()->andReturn(
        new AIResponseDTO(
            content: 'Test response',
            provider: 'openai',
            model: 'gpt-4o',
            inputTokens: 10,
            outputTokens: 20,
            cost: 0.001,
            latencyMs: 150,
        )
    );

    $response = $this->postJson('/api/v1/mcp/messages', [
        'role' => 'user',
        'content' => 'Hello MCP',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.content', 'Test response')
        ->assertJsonStructure([
            'data' => [
                'content', 'provider', 'model',
                'input_tokens', 'output_tokens', 'cost',
            ],
        ]);
});

it('validates MCP message request', function () {
    $response = $this->postJson('/api/v1/mcp/messages', [
        'role' => 'invalid',
        'content' => '',
    ]);

    $response->assertStatus(422);
});

it('returns MCP server status', function () {
    $this->mockServer->shouldReceive('status')->once()->andReturn([
        'providers' => [],
        'token_usage' => [],
        'costs' => [],
        'uptime' => ['status' => 'operational'],
    ]);

    $response = $this->getJson('/api/v1/mcp/status');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['providers', 'token_usage', 'costs']]);
});

it('returns MCP server health', function () {
    $this->mockServer->shouldReceive('health')->once()->andReturn([
        'status' => 'operational',
        'timestamp' => now()->toIso8601String(),
        'providers_count' => 2,
    ]);

    $response = $this->getJson('/api/v1/mcp/health');

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'operational');
});

it('returns providers list', function () {
    $provider = Mockery::mock(\App\Interfaces\AIProviderInterface::class);
    $provider->shouldReceive('models')->andReturn(['gpt-4o']);
    $provider->shouldReceive('isAvailable')->andReturn(true);

    $this->mockServer->shouldReceive('getAvailableProviders')->once()->andReturn([
        'openai' => $provider,
    ]);

    $response = $this->getJson('/api/v1/mcp/providers');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});
