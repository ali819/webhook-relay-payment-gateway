<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\RelayController;

// webhook multiple
Route::post('/webhook/relay-callback', [RelayController::class, 'handle'])->name('handleApi');
