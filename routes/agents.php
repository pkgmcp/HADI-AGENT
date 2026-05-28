<?php

use App\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/agents')->middleware('api')->group(function () {
    Route::get('/', [AgentController::class, 'list']);
    Route::post('/{agent}/execute', [AgentController::class, 'execute']);
    Route::get('/{agent}', [AgentController::class, 'info']);
    Route::post('/{agent}/reset', [AgentController::class, 'reset']);
});
