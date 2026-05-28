<?php

use App\Http\Controllers\MCPController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/ai')->middleware('api')->group(function () {
    Route::post('/complete', [MCPController::class, 'handle']);

    Route::post('/stream', [MCPController::class, 'handle']);

    Route::post('/providers/{provider}/test', function (string $provider) {
        $server = app(\App\Services\MCP\MCPServer::class);
        $routeProvider = $server->getProvider($provider);

        if (!$routeProvider) {
            return response()->json([
                'success' => false,
                'message' => "Provider '{$provider}' is not registered",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $routeProvider->name(),
                'available' => $routeProvider->isAvailable(),
                'models' => $routeProvider->models(),
            ],
        ]);
    });

    Route::get('/usage', function () {
        $observer = app(\App\Services\MCP\TokenObserver::class);
        $analyzer = app(\App\Services\MCP\CostAnalyzer::class);

        return response()->json([
            'data' => [
                'tokens' => $observer->summary(),
                'costs' => $analyzer->summary(),
                'budget' => $analyzer->budgetUtilization(),
            ],
        ]);
    });

    Route::get('/providers', function () {
        $server = app(\App\Services\MCP\MCPServer::class);
        $providers = [];

        foreach ($server->getAvailableProviders() as $name => $provider) {
            $providers[] = [
                'name' => $name,
                'available' => $provider->isAvailable(),
                'models' => $provider->models(),
            ];
        }

        return response()->json(['data' => $providers]);
    });
});
