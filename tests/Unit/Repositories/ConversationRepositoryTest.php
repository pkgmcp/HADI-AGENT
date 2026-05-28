<?php

use App\Models\MCP\Conversation;
use App\Repositories\ConversationRepository;

beforeEach(function () {
    $this->repo = new ConversationRepository;
});

it('creates a conversation', function () {
    $conv = $this->repo->create([
        'session_id' => 'test_session',
        'title' => 'Test Conversation',
        'provider' => 'openai',
        'model' => 'gpt-4o',
    ]);

    expect($conv)->toBeInstanceOf(Conversation::class);
    expect($conv->session_id)->toBe('test_session');
});

it('finds conversation by id', function () {
    $conv = $this->repo->create([
        'session_id' => 'find_by_id',
        'title' => 'Find Me',
    ]);

    $found = $this->repo->findById($conv->id);
    expect($found->id)->toBe($conv->id);
});

it('returns null for non-existent id', function () {
    expect($this->repo->findById(99999))->toBeNull();
});

it('finds conversation by session id', function () {
    $this->repo->create([
        'session_id' => 'active_session',
        'title' => 'Active',
        'status' => 'active',
    ]);

    $found = $this->repo->findBySession('active_session');
    expect($found)->not->toBeNull();
    expect($found->title)->toBe('Active');
});

it('updates a conversation', function () {
    $conv = $this->repo->create([
        'session_id' => 'upd_session',
        'title' => 'Original',
    ]);

    $updated = $this->repo->update($conv->id, ['title' => 'Updated']);
    expect($updated)->toBeTrue();

    expect($this->repo->findById($conv->id)->title)->toBe('Updated');
});

it('deletes a conversation', function () {
    $conv = $this->repo->create([
        'session_id' => 'del_session',
        'title' => 'Delete Me',
    ]);

    $deleted = $this->repo->delete($conv->id);
    expect($deleted)->toBeTrue();

    expect($this->repo->findById($conv->id))->toBeNull();
});

it('adds message to conversation', function () {
    $conv = $this->repo->create([
        'session_id' => 'msg_session',
        'title' => 'Messages',
    ]);

    $msg = $this->repo->addMessage($conv->id, [
        'role' => 'user',
        'content' => 'Hello',
    ]);

    expect($msg->role)->toBe('user');
    expect($msg->content)->toBe('Hello');
});

it('gets messages for conversation', function () {
    $conv = $this->repo->create([
        'session_id' => 'get_msgs',
        'title' => 'Get Messages',
    ]);

    $this->repo->addMessage($conv->id, ['role' => 'user', 'content' => 'A']);
    $this->repo->addMessage($conv->id, ['role' => 'assistant', 'content' => 'B']);

    $messages = $this->repo->getMessages($conv->id);
    expect($messages)->toHaveCount(2);
});

it('returns token usage summary for session', function () {
    $conv = $this->repo->create([
        'session_id' => 'tokens_summary',
        'token_count' => 500,
        'cost' => 0.05,
    ]);

    $summary = $this->repo->getTokenUsageSummary('tokens_summary');
    expect($summary['token_count'])->toBe(500);
    expect($summary['cost'])->toBe(0.05);
});

it('returns empty summary for unknown session', function () {
    $summary = $this->repo->getTokenUsageSummary('unknown');
    expect($summary['token_count'])->toBe(0);
});
