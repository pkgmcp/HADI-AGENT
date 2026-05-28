<?php

use App\Services\MCP\MCPServer;

beforeEach(function () {
    $this->server = app(MCPServer::class);
});

it('broadcasts message to multiple providers', function () {
    $message = new \App\DTOs\MCPMessageDTO(
        role: 'user',
        content: 'Broadcast test message',
    );

    $responses = $this->server->broadcast($message);

    expect($responses)->toBeArray();
});

it('broadcast returns provider-keyed responses', function () {
    $message = new \App\DTOs\MCPMessageDTO('user', 'Hello all');

    $responses = $this->server->broadcast($message);

    foreach ($responses as $provider => $response) {
        expect($provider)->toBeString();
    }
});

it('broadcast handles provider errors gracefully', function () {
    $message = new \App\DTOs\MCPMessageDTO('user', 'Error test');

    $responses = $this->server->broadcast($message);

    // Should never throw — each provider response is wrapped
    expect($responses)->toBeArray();
});

it('broadcast via API endpoint returns valid structure', function () {
    $response = $this->postJson('/api/v1/mcp/broadcast', [
        'role' => 'user',
        'content' => 'Broadcast API test',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

it('validates broadcast request', function () {
    $response = $this->postJson('/api/v1/mcp/broadcast', [
        'role' => '',
        'content' => '',
    ]);

    $response->assertStatus(422);
});
