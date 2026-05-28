<?php

use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/workflows')->middleware('api')->group(function () {
    Route::post('/', [WorkflowController::class, 'create']);
    Route::post('/{id}/execute', [WorkflowController::class, 'execute']);
    Route::get('/{id}/status', [WorkflowController::class, 'status']);
    Route::get('/{id}/step/{step}', [WorkflowController::class, 'step']);
    Route::post('/{id}/cancel', [WorkflowController::class, 'cancel']);
    Route::post('/{id}/retry', [WorkflowController::class, 'retry']);
});
