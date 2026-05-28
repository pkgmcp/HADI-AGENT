<?php

namespace App\Repositories;

use App\Models\MCP\Conversation;
use App\Models\MCP\ConversationMessage;

class ConversationRepository
{
    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }

    public function findById(int $id): ?Conversation
    {
        return Conversation::find($id);
    }

    public function findBySession(string $sessionId): ?Conversation
    {
        return Conversation::bySession($sessionId)->active()->first();
    }

    public function update(int $id, array $data): bool
    {
        return Conversation::findOrFail($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Conversation::findOrFail($id)->delete();
    }

    public function addMessage(int $conversationId, array $data): ConversationMessage
    {
        return ConversationMessage::create(array_merge($data, [
            'conversation_id' => $conversationId,
        ]));
    }

    public function getMessages(int $conversationId): iterable
    {
        return ConversationMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get();
    }

    public function getRecentConversations(int $limit = 10): iterable
    {
        return Conversation::withCount('messages')
            ->where('status', 'active')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTokenUsageSummary(string $sessionId): array
    {
        $conversation = $this->findBySession($sessionId);

        if (!$conversation) {
            return [
                'token_count' => 0,
                'cost' => 0,
                'message_count' => 0,
            ];
        }

        return [
            'token_count' => $conversation->token_count,
            'cost' => $conversation->cost,
            'message_count' => $conversation->messages()->count(),
        ];
    }
}
