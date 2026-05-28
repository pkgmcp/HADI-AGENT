<?php

return [
    'routing' => [
        'strategy' => env('AI_ROUTING_STRATEGY', 'latency'),
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
        'fallback_provider' => env('AI_FALLBACK_PROVIDER', 'ollama'),
        'load_balance' => env('AI_LOAD_BALANCE', false),
    ],

    'rate_limiting' => [
        'enabled' => env('AI_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => env('AI_MAX_REQUESTS_PER_MINUTE', 60),
        'max_tokens_per_minute' => env('AI_MAX_TOKENS_PER_MINUTE', 100000),
    ],

    'cost_management' => [
        'budget_limit' => env('AI_BUDGET_LIMIT', 100.00),
        'currency' => env('AI_COST_CURRENCY', 'USD'),
        'alert_email' => env('AI_ALERT_EMAIL'),
    ],

    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600),
        'driver' => env('AI_CACHE_DRIVER', 'redis'),
    ],

    'moderation' => [
        'enabled' => env('AI_MODERATION_ENABLED', true),
        'provider' => env('AI_MODERATION_PROVIDER', 'openai'),
    ],

    'logging' => [
        'channel' => env('AI_LOG_CHANNEL', 'daily'),
        'level' => env('AI_LOG_LEVEL', 'debug'),
        'track_prompts' => env('AI_TRACK_PROMPTS', true),
        'track_responses' => env('AI_TRACK_RESPONSES', true),
    ],
];
