<?php

namespace App\Services\MCP;

use App\DTOs\MCPMessageDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContextManager
{
    private array $conversations = [];

    private int $maxContextSize;

    public function __construct()
    {
        $this->maxContextSize = config('mcp.context_management.max_context_size', 8192);
    }

    public function build(MCPMessageDTO $message): array
    {
        $sessionId = $message->metadata['session_id'] ?? $this->generateSessionId();

        $conversation = $this->loadConversation($sessionId);

        $conversation[] = [
            'role' => $message->role,
            'content' => $message->content,
        ];

        $conversation = $this->trimContext($conversation);

        $this->conversations[$sessionId] = $conversation;

        return $conversation;
    }

    public function addContext(string $sessionId, array $messages): void
    {
        $conversation = $this->loadConversation($sessionId);

        foreach ($messages as $message) {
            $conversation[] = $message;
        }

        $this->conversations[$sessionId] = $this->trimContext($conversation);

        $this->persistConversation($sessionId, $this->conversations[$sessionId]);
    }

    public function getContext(string $sessionId): array
    {
        return $this->loadConversation($sessionId);
    }

    public function clearContext(string $sessionId): void
    {
        unset($this->conversations[$sessionId]);
        Cache::forget("mcp_context_{$sessionId}");
    }

    public function getContextSize(string $sessionId): int
    {
        $conversation = $this->loadConversation($sessionId);
        $totalTokens = 0;

        foreach ($conversation as $message) {
            $totalTokens += $this->estimateTokens($message['content'] ?? '');
        }

        return $totalTokens;
    }

    public function summarize(string $sessionId): string
    {
        $conversation = $this->loadConversation($sessionId);

        if (empty($conversation)) {
            return '';
        }

        $fullText = '';
        foreach ($conversation as $msg) {
            $fullText .= "{$msg['role']}: {$msg['content']}\n";
        }

        $words = str_word_count($fullText);
        $summary = "Conversation has " . count($conversation) . " messages, ~{$words} words.";

        if ($words > 100) {
            $summary .= " First message: " . mb_substr($conversation[0]['content'] ?? '', 0, 200);
        }

        return $summary;
    }

    public function allSessions(): array
    {
        return array_keys($this->conversations);
    }

    private function loadConversation(string $sessionId): array
    {
        if (isset($this->conversations[$sessionId])) {
            return $this->conversations[$sessionId];
        }

        $cached = Cache::get("mcp_context_{$sessionId}");

        if ($cached) {
            $this->conversations[$sessionId] = $cached;
        }

        return $this->conversations[$sessionId] ?? [];
    }

    private function trimContext(array $conversation): array
    {
        $tokenCount = 0;
        $trimmed = [];

        foreach (array_reverse($conversation) as $message) {
            $tokens = $this->estimateTokens($message['content'] ?? '');
            if ($tokenCount + $tokens > $this->maxContextSize) {
                $trimmed[] = [
                    'role' => 'system',
                    'content' => '[Context trimmed due to length]',
                ];
                break;
            }
            $tokenCount += $tokens;
            array_unshift($trimmed, $message);
        }

        return $trimmed;
    }

    private function estimateTokens(string $text): int
    {
        return (int)ceil(mb_strlen($text) / 4);
    }

    private function persistConversation(string $sessionId, array $conversation): void
    {
        try {
            Cache::put(
                "mcp_context_{$sessionId}",
                $conversation,
                now()->addHours(24)
            );
        } catch (\Throwable $e) {
            Log::error('Failed to persist conversation context', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateSessionId(): string
    {
        return 'mcp_' . bin2hex(random_bytes(16));
    }
}
