<?php

return [
    'default_agent' => env('HADI_DEFAULT_AGENT', 'planner'),

    'agents' => [
        'planner' => [
            'class' => App\Services\Agents\PlannerAgent::class,
            'description' => 'Analyzes requests and generates execution plans',
            'provider' => env('PLANNER_PROVIDER', 'openai'),
            'model' => env('PLANNER_MODEL', 'gpt-4o'),
            'temperature' => env('PLANNER_TEMPERATURE', 0.3),
            'max_iterations' => env('PLANNER_MAX_ITERATIONS', 3),
        ],
        'coder' => [
            'class' => App\Services\Agents\CoderAgent::class,
            'description' => 'Generates production-ready code',
            'provider' => env('CODER_PROVIDER', 'anthropic'),
            'model' => env('CODER_MODEL', 'claude-3-opus-20240229'),
            'temperature' => env('CODER_TEMPERATURE', 0.2),
            'max_iterations' => env('CODER_MAX_ITERATIONS', 5),
        ],
        'reviewer' => [
            'class' => App\Services\Agents\ReviewAgent::class,
            'description' => 'Reviews code for quality, security, and bugs',
            'provider' => env('REVIEWER_PROVIDER', 'openai'),
            'model' => env('REVIEWER_MODEL', 'gpt-4o'),
            'temperature' => env('REVIEWER_TEMPERATURE', 0.1),
            'max_iterations' => env('REVIEWER_MAX_ITERATIONS', 3),
        ],
        'debugger' => [
            'class' => App\Services\Agents\DebuggerAgent::class,
            'description' => 'Detects and fixes bugs in code',
            'provider' => env('DEBUGGER_PROVIDER', 'anthropic'),
            'model' => env('DEBUGGER_MODEL', 'claude-3-opus-20240229'),
            'temperature' => env('DEBUGGER_TEMPERATURE', 0.2),
            'max_iterations' => env('DEBUGGER_MAX_ITERATIONS', 5),
        ],
        'memory' => [
            'class' => App\Services\Agents\MemoryAgent::class,
            'description' => 'Manages conversation context and vector memory',
            'provider' => env('MEMORY_PROVIDER', 'openai'),
            'model' => env('MEMORY_MODEL', 'gpt-4o'),
            'temperature' => env('MEMORY_TEMPERATURE', 0.1),
            'max_iterations' => env('MEMORY_MAX_ITERATIONS', 2),
        ],
        'security' => [
            'class' => App\Services\Agents\SecurityAgent::class,
            'description' => 'Analyzes code for security vulnerabilities',
            'provider' => env('SECURITY_PROVIDER', 'anthropic'),
            'model' => env('SECURITY_MODEL', 'claude-3-opus-20240229'),
            'temperature' => env('SECURITY_TEMPERATURE', 0.1),
            'max_iterations' => env('SECURITY_MAX_ITERATIONS', 3),
        ],
        'deployment' => [
            'class' => App\Services\Agents\DeploymentAgent::class,
            'description' => 'Manages deployment and release workflows',
            'provider' => env('DEPLOYMENT_PROVIDER', 'openai'),
            'model' => env('DEPLOYMENT_MODEL', 'gpt-4o'),
            'temperature' => env('DEPLOYMENT_TEMPERATURE', 0.3),
            'max_iterations' => env('DEPLOYMENT_MAX_ITERATIONS', 3),
        ],
        'workflow' => [
            'class' => App\Services\Agents\WorkflowAgent::class,
            'description' => 'Orchestrates multi-agent workflows',
            'provider' => env('WORKFLOW_AGENT_PROVIDER', 'openai'),
            'model' => env('WORKFLOW_AGENT_MODEL', 'gpt-4o'),
            'temperature' => env('WORKFLOW_AGENT_TEMPERATURE', 0.2),
            'max_iterations' => env('WORKFLOW_AGENT_MAX_ITERATIONS', 10),
        ],
    ],

    'orchestration' => [
        'max_concurrent_agents' => env('MAX_CONCURRENT_AGENTS', 5),
        'timeout_per_agent' => env('AGENT_TIMEOUT', 120),
        'retry_on_failure' => env('AGENT_RETRY_ON_FAILURE', true),
        'max_retries' => env('AGENT_MAX_RETRIES', 2),
    ],

    'memory' => [
        'store_conversations' => env('AGENT_STORE_CONVERSATIONS', true),
        'vector_search_enabled' => env('AGENT_VECTOR_SEARCH', true),
        'context_window' => env('AGENT_CONTEXT_WINDOW', 50),
    ],
];
