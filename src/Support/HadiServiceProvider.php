<?php

namespace Hadi\HadiAgent\Support;

use Illuminate\Support\ServiceProvider;

class HadiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/agents.php', 'agents');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/agents.php' => config_path('agents.php'),
            __DIR__ . '/../../config/ai.php' => config_path('ai.php'),
            __DIR__ . '/../../config/mcp.php' => config_path('mcp.php'),
        ], 'hadi-config');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
            $this->loadRoutesFrom(__DIR__ . '/../../routes/mcp.php');
            $this->loadRoutesFrom(__DIR__ . '/../../routes/agents.php');
            $this->loadRoutesFrom(__DIR__ . '/../../routes/workflows.php');
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'hadi');
        }
    }
}
