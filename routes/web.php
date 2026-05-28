<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard.index')->name('dashboard');
Route::view('/agents', 'agents.index')->name('agents.index');
Route::view('/mcp', 'mcp.index')->name('mcp.index');
