<?php

namespace App\Support;

use App\Http\Controllers\AgentController;
use App\Interfaces\AIProviderInterface;
use App\Interfaces\MCPServerInterface;
use App\Interfaces\WorkflowEngineInterface;
use App\Services\AI\AnthropicProvider;
use App\Services\AI\DeepSeekProvider;
use App\Services\AI\GeminiProvider;
use App\Services\AI\LMStudioProvider;
use App\Services\AI\OllamaProvider;
use App\Services\AI\OpenAIProvider;
use App\Services\MCP\MCPServer;
use App\Services\MCP\WorkflowEngine;
use App\Services\Agents\CoderAgent;
use App\Services\Agents\DebuggerAgent;
use App\Services\Agents\DeploymentAgent;
use App\Services\Agents\MemoryAgent;
use App\Services\Agents\PlannerAgent;
use App\Services\Agents\ReviewAgent;
use App\Services\Agents\SecurityAgent;
use App\Services\Agents\WorkflowAgent;
use Illuminate\Support\ServiceProvider;

class HadiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MCPServerInterface::class, MCPServer::class);
        $this->app->singleton(WorkflowEngineInterface::class, WorkflowEngine::class);

        $this->app->singleton(MCPServer::class, function ($app) {
            $server = new MCPServer;

            $this->registerProviders($server);

            return $server;
        });

        $this->app->singleton(AgentController::class, function ($app) {
            $controller = new AgentController;

            $this->registerAgents($controller);

            return $controller;
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/mcp.php'));
        $this->loadRoutesFrom(base_path('routes/agents.php'));
        $this->loadRoutesFrom(base_path('routes/workflows.php'));

        $this->publishes([
            __DIR__ . '/../../config/mcp.php' => config_path('mcp.php'),
            __DIR__ . '/../../config/agents.php' => config_path('agents.php'),
            __DIR__ . '/../../config/ai.php' => config_path('ai.php'),
        ], 'hadi-config');

        $this->publishesMigrations([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'hadi-migrations');
    }

    private function registerProviders(MCPServer $server): void
    {
        $providers = [
            'openai' => OpenAIProvider::class,
            'anthropic' => AnthropicProvider::class,
            'gemini' => GeminiProvider::class,
            'ollama' => OllamaProvider::class,
            'deepseek' => DeepSeekProvider::class,
            'lm_studio' => LMStudioProvider::class,
        ];

        foreach ($providers as $name => $class) {
            if (class_exists($class)) {
                try {
                    $provider = app($class);
                    if ($provider->isAvailable()) {
                        $server->registerProvider($name, $provider);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    private function registerAgents(AgentController $controller): void
    {
        $agents = [
            'planner' => PlannerAgent::class,
            'coder' => CoderAgent::class,
            'reviewer' => ReviewAgent::class,
            'debugger' => DebuggerAgent::class,
            'memory' => MemoryAgent::class,
            'security' => SecurityAgent::class,
            'deployment' => DeploymentAgent::class,
            'workflow' => WorkflowAgent::class,
        ];

        foreach ($agents as $name => $class) {
            if (class_exists($class) && config("agents.agents.{$name}")) {
                try {
                    $controller->register($name, app($class));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }
}
