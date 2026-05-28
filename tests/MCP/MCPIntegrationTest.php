<?php

use App\Services\MCP\MCPServer;
use App\Services\MCP\ProviderRouter;

it('MCP server resolves from container', function () {
    $server = app(MCPServer::class);
    expect($server)->toBeInstanceOf(MCPServer::class);
});

it('ProviderRouter resolves from container', function () {
    $router = app(ProviderRouter::class);
    expect($router)->toBeInstanceOf(ProviderRouter::class);
});

it('can register and list available providers', function () {
    $server = app(MCPServer::class);
    $providers = $server->getAvailableProviders();

    expect($providers)->toBeArray();
});

it('MCP server health returns valid structure', function () {
    $server = app(MCPServer::class);
    $health = $server->health();

    expect($health)->toHaveKeys(['status', 'timestamp', 'providers_count', 'available_providers']);
    expect($health['status'])->toBe('operational');
    expect($health['providers_count'])->toBeInt();
});

it('MCP server status returns all sections', function () {
    $server = app(MCPServer::class);
    $status = $server->status();

    expect($status)->toHaveKeys(['providers', 'token_usage', 'costs', 'uptime']);
});

it('broadcast returns array of responses', function () {
    $server = app(MCPServer::class);

    $message = new \App\DTOs\MCPMessageDTO(
        role: 'user',
        content: 'Test broadcast',
    );

    $responses = $server->broadcast($message);
    expect($responses)->toBeArray();
});
