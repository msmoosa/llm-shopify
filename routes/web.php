<?php

use App\Http\Controllers\GenerateController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['verify.shopify'])->name('home');

// API endpoint: GET /api/generate (no CSRF required)
Route::get('/api/generate', [GenerateController::class, 'index'])
    ->middleware(['verify.shopify']);
