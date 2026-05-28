<?php

use App\DTOs\MCPMessageDTO;
use App\Services\MCP\ContextManager;

beforeEach(function () {
    $this->manager = new ContextManager;
});

it('builds context from a message', function () {
    $message = new MCPMessageDTO(
        role: 'user',
        content: 'Hello',
        metadata: ['session_id' => 'test_1'],
    );

    $context = $this->manager->build($message);

    expect($context)->toBeArray();
    expect($context[0]['role'])->toBe('user');
    expect($context[0]['content'])->toBe('Hello');
});

it('accumulates messages in the same session', function () {
    $msg1 = new MCPMessageDTO('user', 'First', metadata: ['session_id' => 'acc_test']);
    $msg2 = new MCPMessageDTO('user', 'Second', metadata: ['session_id' => 'acc_test']);

    $this->manager->build($msg1);
    $context = $this->manager->build($msg2);

    expect($context)->toHaveCount(2);
    expect($context[1]['content'])->toBe('Second');
});

it('adds context messages to a session', function () {
    $this->manager->addContext('ctx_test', [
        ['role' => 'system', 'content' => 'You are a bot'],
        ['role' => 'user', 'content' => 'Hello'],
    ]);

    $context = $this->manager->getContext('ctx_test');
    expect($context)->toHaveCount(2);
});

it('returns empty array for unknown session', function () {
    expect($this->manager->getContext('unknown'))->toBe([]);
});

it('clears context for a session', function () {
    $message = new MCPMessageDTO('user', 'Hi', metadata: ['session_id' => 'clear_test']);
    $this->manager->build($message);

    $this->manager->clearContext('clear_test');
    expect($this->manager->getContext('clear_test'))->toBe([]);
});

it('reports context size', function () {
    $message = new MCPMessageDTO('user', str_repeat('a', 400), metadata: ['session_id' => 'size_test']);
    $this->manager->build($message);

    $size = $this->manager->getContextSize('size_test');
    expect($size)->toBeGreaterThan(0);
});

it('generates summary for a session', function () {
    $msg1 = new MCPMessageDTO('user', 'What is Laravel?', metadata: ['session_id' => 'sum_test']);
    $msg2 = new MCPMessageDTO('assistant', 'Laravel is a PHP framework.', metadata: ['session_id' => 'sum_test']);

    $this->manager->build($msg1);
    $this->manager->build($msg2);

    $summary = $this->manager->summarize('sum_test');
    expect($summary)->toContain('messages');
});

it('lists all active sessions', function () {
    $m1 = new MCPMessageDTO('user', 'A', metadata: ['session_id' => 's1']);
    $m2 = new MCPMessageDTO('user', 'B', metadata: ['session_id' => 's2']);

    $this->manager->build($m1);
    $this->manager->build($m2);

    $sessions = $this->manager->allSessions();
    expect($sessions)->toContain('s1');
    expect($sessions)->toContain('s2');
});

it('returns empty string for empty session summary', function () {
    expect($this->manager->summarize('empty_session'))->toBe('');
});

it('handles messages without session id gracefully', function () {
    $message = new MCPMessageDTO('user', 'No session');
    $context = $this->manager->build($message);
    expect($context)->toHaveCount(1);
});
