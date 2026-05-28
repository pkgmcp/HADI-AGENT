<?php

use App\Services\MCP\PromptReducer;

beforeEach(function () {
    $this->reducer = new PromptReducer;
});

it('does not reduce context under threshold', function () {
    $context = [
        ['role' => 'user', 'content' => 'Hello'],
        ['role' => 'assistant', 'content' => 'Hi there'],
    ];

    $reduced = $this->reducer->reduce($context);

    expect($reduced)->toBe($context);
});

it('returns empty string for empty messages', function () {
    $result = $this->reducer->foldMessages([]);
    expect($result)->toBe('');
});

it('compresses prompt by removing extra blank lines', function () {
    $prompt = "Line 1\n\n\n\nLine 2\n\nLine 3";
    $compressed = $this->reducer->compressPrompt($prompt);

    expect($compressed)->not->toContain("\n\n\n");
});

it('truncates text to token limit', function () {
    $longText = str_repeat('a ', 2000);
    $truncated = $this->reducer->truncateToTokens($longText, 100);

    expect(mb_strlen($truncated))->toBeLessThan(mb_strlen($longText));
    expect($truncated)->toEndWith('[Content truncated...]');
});

it('extracts key info as first sentences', function () {
    $text = 'First important sentence. Second one. Third one. Fourth one. Fifth one.';
    $extracted = $this->reducer->extractKeyInfo($text, 3);

    expect($extracted)->toContain('First important sentence');
    expect($extracted)->not->toContain('Fifth one');
});
