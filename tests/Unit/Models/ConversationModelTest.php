<?php

use App\Models\MCP\Conversation;
use App\Models\MCP\ConversationMessage;

it('has fillable attributes', function () {
    $conv = new Conversation;

    expect($conv->getFillable())->toContain('session_id');
    expect($conv->getFillable())->toContain('title');
    expect($conv->getFillable())->toContain('status');
});

it('casts attributes correctly', function () {
    $conv = new Conversation;

    expect($conv->getCasts()['context'])->toBe('array');
    expect($conv->getCasts()['metadata'])->toBe('array');
    expect($conv->getCasts()['token_count'])->toBe('integer');
    expect($conv->getCasts()['cost'])->toBe('float');
});

it('has messages relationship', function () {
    $conv = Conversation::factory()->make();

    expect($conv->messages())->toBeInstanceOf(
        Illuminate\Database\Eloquent\Relations\HasMany::class
    );
});

it('scopes active conversations', function () {
    Conversation::factory()->count(3)->create(['status' => 'active']);
    Conversation::factory()->create(['status' => 'archived']);

    expect(Conversation::active()->count())->toBe(3);
});

it('scopes by session id', function () {
    Conversation::factory()->create(['session_id' => 'unique_session']);

    expect(Conversation::bySession('unique_session')->count())->toBe(1);
});

it('has many messages', function () {
    $conv = Conversation::factory()
        ->has(ConversationMessage::factory()->count(3))
        ->create();

    expect($conv->messages)->toHaveCount(3);
});

it('creates message through relationship', function () {
    $conv = Conversation::factory()->create();

    $msg = $conv->messages()->create([
        'role' => 'user',
        'content' => 'Test message',
    ]);

    expect($msg)->toBeInstanceOf(ConversationMessage::class);
    expect($msg->conversation_id)->toBe($conv->id);
});
