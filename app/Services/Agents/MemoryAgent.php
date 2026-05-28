<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;
use App\Interfaces\MemoryInterface;
use Illuminate\Support\Facades\Cache;

class MemoryAgent extends BaseAgent implements MemoryInterface
{
    private array $localStore = [];

    private int $contextWindow;

    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'MemoryAgent',
            type: 'memory',
            provider: config('agents.agents.memory.provider', 'openai'),
            model: config('agents.agents.memory.model', 'gpt-4o'),
            temperature: config('agents.agents.memory.temperature', 0.1),
            maxIterations: config('agents.agents.memory.max_iterations', 2),
            config: config('agents.agents.memory'),
        );

        $this->contextWindow = config('agents.memory.context_window', 50);
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['remember', 'memory', 'store', 'recall', 'context', 'history', 'conversation'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function store(string $key, mixed $value, array $metadata = []): void
    {
        $entry = [
            'value' => $value,
            'metadata' => $metadata,
            'timestamp' => now()->toIso8601String(),
        ];

        $this->localStore[$key] = $entry;

        Cache::put("memory_{$key}", $entry, now()->addDays(30));
    }

    public function retrieve(string $key): mixed
    {
        if (isset($this->localStore[$key])) {
            return $this->localStore[$key]['value'];
        }

        $cached = Cache::get("memory_{$key}");

        return $cached['value'] ?? null;
    }

    public function search(string $query, int $limit = 5): array
    {
        $results = [];
        $queryLower = strtolower($query);

        foreach ($this->localStore as $key => $entry) {
            $valueStr = is_string($entry['value']) ? $entry['value'] : json_encode($entry['value']);

            if (str_contains(strtolower($key), $queryLower) || str_contains(strtolower($valueStr), $queryLower)) {
                $results[] = [
                    'key' => $key,
                    'value' => $entry['value'],
                    'metadata' => $entry['metadata'],
                    'timestamp' => $entry['timestamp'],
                    'relevance' => $this->calculateRelevance($query, $key, $valueStr),
                ];
            }
        }

        usort($results, fn($a, $b) => $b['relevance'] <=> $a['relevance']);

        return array_slice($results, 0, $limit);
    }

    public function forget(string $key): bool
    {
        unset($this->localStore[$key]);
        Cache::forget("memory_{$key}");

        return true;
    }

    public function clear(): bool
    {
        $this->localStore = [];

        return true;
    }

    public function context(): array
    {
        $entries = array_slice($this->localStore, -$this->contextWindow);

        $context = [];
        foreach ($entries as $key => $entry) {
            $context[$key] = $entry['value'];
        }

        return $context;
    }

    public function summarizeMemory(): string
    {
        $response = $this->execute(
            "Summarize the current memory state:\n\n" .
            json_encode($this->localStore, JSON_PRETTY_PRINT) . "\n\n" .
            "Provide a concise summary of what's stored and any patterns noticed."
        );

        return $response->content;
    }

    public function getConversationHistory(string $sessionId): array
    {
        return Cache::get("conversation_{$sessionId}", []);
    }

    public function storeConversation(string $sessionId, array $messages): void
    {
        Cache::put("conversation_{$sessionId}", $messages, now()->addDays(7));
    }

    public function getStats(): array
    {
        return [
            'stored_items' => count($this->localStore),
            'context_window' => $this->contextWindow,
            'cached_keys' => count(array_keys($this->localStore)),
        ];
    }

    protected function systemPrompt(): string
    {
        return "You are MemoryAgent, responsible for maintaining context and memory across conversations.\n\n" .
            "Your role:\n" .
            "- Store and retrieve information efficiently\n" .
            "- Maintain conversation context\n" .
            "- Summarize and compress memories\n" .
            "- Identify relevant information for current tasks\n" .
            "- Manage context window effectively\n\n" .
            "You help other agents by providing relevant context from past interactions.";
    }

    private function calculateRelevance(string $query, string $key, string $value): float
    {
        $queryTerms = explode(' ', strtolower($query));
        $keyLower = strtolower($key);
        $valueLower = strtolower($value);

        $score = 0;

        foreach ($queryTerms as $term) {
            if (str_contains($keyLower, $term)) {
                $score += 3;
            }
            if (str_contains($valueLower, $term)) {
                $score += 1;
            }
        }

        return $score;
    }
}
