<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Log;

class PromptReducer
{
    private int $summarizationThreshold;

    private int $maxTokens;

    public function __construct()
    {
        $this->summarizationThreshold = config('mcp.context_management.summarization_threshold', 4000);
        $this->maxTokens = config('mcp.context_management.max_context_size', 8192);
    }

    public function reduce(array $context): array
    {
        $totalTokens = $this->countTokens($context);

        if ($totalTokens <= $this->summarizationThreshold) {
            return $context;
        }

        return $this->compress($context);
    }

    public function compress(array $context): array
    {
        $reduced = [];
        $tokenCount = 0;
        $systemMessages = [];
        $recentMessages = [];

        foreach ($context as $message) {
            if (($message['role'] ?? '') === 'system') {
                $systemMessages[] = $message;
            } else {
                $recentMessages[] = $message;
            }
        }

        $reduced = $systemMessages;

        $summarized = [];
        $keepRecent = [];
        $summarizationPoint = max(0, count($recentMessages) - 10);

        foreach ($recentMessages as $i => $message) {
            if ($i < $summarizationPoint) {
                $summarized[] = $message;
            } else {
                $keepRecent[] = $message;
            }
        }

        if (!empty($summarized)) {
            $compressed = $this->foldMessages($summarized);
            if ($compressed) {
                $reduced[] = [
                    'role' => 'system',
                    'content' => "[Previous conversation summarized]: {$compressed}",
                ];
            }
        }

        foreach ($keepRecent as $message) {
            $tokens = $this->estimateTokens($message['content'] ?? '');
            if ($tokenCount + $tokens > $this->maxTokens) {
                break;
            }
            $tokenCount += $tokens;
            $reduced[] = $message;
        }

        return $reduced;
    }

    public function foldMessages(array $messages): string
    {
        if (empty($messages)) {
            return '';
        }

        $exchanges = [];
        foreach ($messages as $msg) {
            $content = $msg['content'] ?? '';
            $content = mb_substr($content, 0, 500);
            $exchanges[] = "{$msg['role']}: {$content}";
        }

        $folded = implode(' | ', $exchanges);

        if (mb_strlen($folded) > 2000) {
            $folded = mb_substr($folded, 0, 2000) . '...';
        }

        return $folded;
    }

    public function compressPrompt(string $prompt): string
    {
        $lines = explode("\n", $prompt);
        $compressed = [];
        $blankCount = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                $blankCount++;
                if ($blankCount <= 1) {
                    $compressed[] = '';
                }
            } else {
                $blankCount = 0;
                $compressed[] = $trimmed;
            }
        }

        return implode("\n", $compressed);
    }

    public function truncateToTokens(string $text, int $maxTokens): string
    {
        $currentTokens = $this->estimateTokens($text);

        if ($currentTokens <= $maxTokens) {
            return $text;
        }

        $ratio = $maxTokens / $currentTokens;
        $maxChars = (int)(mb_strlen($text) * $ratio);

        return mb_substr($text, 0, $maxChars) . "\n\n[Content truncated...]";
    }

    public function extractKeyInfo(string $text, int $maxSentences = 3): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (count($sentences) <= $maxSentences) {
            return $text;
        }

        $important = array_slice($sentences, 0, $maxSentences);
        $important[] = '[Additional content summarized...]';

        return implode(' ', $important);
    }

    private function countTokens(array $context): int
    {
        $total = 0;
        foreach ($context as $message) {
            $total += $this->estimateTokens($message['content'] ?? '');
        }
        return $total;
    }

    private function estimateTokens(string $text): int
    {
        return (int)ceil(mb_strlen($text) / 4);
    }
}
