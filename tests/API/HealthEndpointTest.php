<?php

it('returns health status', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'service',
            'version',
            'timestamp',
        ])
        ->assertJsonPath('service', 'HADI Agent');
});

it('returns system status', function () {
    $response = $this->getJson('/api/v1/status');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'agents',
            'providers',
            'environment',
        ]);
});

it('returns dashboard page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns agents page', function () {
    $response = $this->get('/agents');

    $response->assertStatus(200);
});

it('returns mcp page', function () {
    $response = $this->get('/mcp');

    $response->assertStatus(200);
});
