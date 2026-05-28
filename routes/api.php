<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'operational',
            'service' => 'HADI Agent',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/status', function () {
        return response()->json([
            'agents' => config('agents.agents', []),
            'providers' => config('mcp.providers', []),
            'environment' => app()->environment(),
        ]);
    });
});
