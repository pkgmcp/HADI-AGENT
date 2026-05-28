<?php

use App\Http\Controllers\MCPController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/mcp')->middleware('api')->group(function () {
    Route::post('/messages', [MCPController::class, 'handle']);
    Route::post('/messages/stream', [MCPController::class, 'handle']);
    Route::post('/broadcast', [MCPController::class, 'broadcast']);
    Route::get('/status', [MCPController::class, 'status']);
    Route::get('/health', [MCPController::class, 'health']);
    Route::get('/providers', [MCPController::class, 'providers']);
});
