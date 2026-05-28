<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/auth')->middleware('api')->group(function () {
    Route::post('/login', function () {
        return response()->json(['message' => 'Login endpoint — implement with Laravel Sanctum or Fortify']);
    });

    Route::post('/register', function () {
        return response()->json(['message' => 'Register endpoint — implement with Laravel Fortify']);
    });

    Route::post('/logout', function () {
        return response()->json(['message' => 'Logout endpoint']);
    })->middleware('auth:sanctum');

    Route::get('/user', function () {
        return response()->json(['data' => request()->user()]);
    })->middleware('auth:sanctum');
});
