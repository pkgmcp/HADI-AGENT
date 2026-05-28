<?php

return [
    'default_provider' => env('MCP_DEFAULT_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 4096),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'endpoint' => env('ANTHROPIC_ENDPOINT', 'https://api.anthropic.com/v1'),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 4096),
            'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1'),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 4096),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama2'),
            'max_tokens' => env('OLLAMA_MAX_TOKENS', 4096),
            'temperature' => env('OLLAMA_TEMPERATURE', 0.7),
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'endpoint' => env('DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/v1'),
            'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 4096),
            'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
        ],
        'lm_studio' => [
            'base_url' => env('LM_STUDIO_BASE_URL', 'http://localhost:1234'),
            'model' => env('LM_STUDIO_MODEL', 'local-model'),
            'max_tokens' => env('LM_STUDIO_MAX_TOKENS', 4096),
            'temperature' => env('LM_STUDIO_TEMPERATURE', 0.7),
        ],
    ],

    'token_tracking' => [
        'enabled' => env('MCP_TOKEN_TRACKING', true),
        'storage' => env('MCP_TOKEN_STORAGE', 'database'),
        'alert_threshold' => env('MCP_TOKEN_ALERT_THRESHOLD', 100000),
    ],

    'context_management' => [
        'max_context_size' => env('MCP_MAX_CONTEXT_SIZE', 8192),
        'compression_enabled' => env('MCP_COMPRESSION_ENABLED', true),
        'summarization_threshold' => env('MCP_SUMMARIZATION_THRESHOLD', 4000),
    ],

    'workflow' => [
        'max_retries' => env('MCP_WORKFLOW_MAX_RETRIES', 3),
        'timeout' => env('MCP_WORKFLOW_TIMEOUT', 300),
        'queue' => env('MCP_WORKFLOW_QUEUE', 'default'),
    ],

    'memory' => [
        'driver' => env('MCP_MEMORY_DRIVER', 'database'),
        'vector_dimensions' => env('MCP_VECTOR_DIMENSIONS', 1536),
        'similarity_threshold' => env('MCP_SIMILARITY_THRESHOLD', 0.75),
    ],
];
