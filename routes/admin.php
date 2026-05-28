<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::view('/', 'admin.dashboard')->name('admin.dashboard');
});
