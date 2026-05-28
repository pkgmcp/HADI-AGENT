<?php

use App\Services\MCP\TokenObserver;

beforeEach(function () {
    $this->observer = new TokenObserver;
});

it('tracks token usage', function () {
    $this->observer->track(
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 100,
        outputTokens: 50,
        cost: 0.002,
        latencyMs: 150,
        sessionId: 'test_session',
        action: 'test',
    );

    $summary = $this->observer->summary('test_session');
    expect($summary['session_id'])->toBe('test_session');
});

it('provides session summary', function () {
    $this->observer->track(
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 10,
        outputTokens: 20,
        cost: 0.001,
        latencyMs: 100,
        sessionId: 'session_1',
        action: 'test',
    );

    $this->observer->track(
        provider: 'anthropic',
        model: 'claude-3',
        inputTokens: 30,
        outputTokens: 40,
        cost: 0.002,
        latencyMs: 200,
        sessionId: 'session_2',
        action: 'test',
    );

    $summary = $this->observer->summary();

    expect($summary['total_sessions'])->toBe(2);
});

it('resets session tracking', function () {
    $this->observer->track(
        provider: 'openai',
        model: 'gpt-4o',
        inputTokens: 10,
        outputTokens: 20,
        cost: 0.001,
        latencyMs: 100,
        sessionId: 'reset_session',
        action: 'test',
    );

    expect($this->observer->getSessionTokens('reset_session'))->toBe(30);

    $this->observer->resetSession('reset_session');
    expect($this->observer->getSessionTokens('reset_session'))->toBe(0);
});
