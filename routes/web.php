<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Panel\DomainController;
use App\Http\Controllers\Panel\LogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('panel.domains.index');
    }
    return redirect()->route('login');
});

Route::middleware(['guest', 'panel.enabled'])->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.post')
        ->middleware('throttle:3,1'); // max (total) attempt per 1 menit per IP
});

Route::middleware(['auth', 'panel.enabled'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', fn() => redirect()->route('panel.domains.index'));
    Route::resource('domains', DomainController::class);

    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::delete('logs/prune', [LogController::class, 'prune'])->name('logs.prune');
    Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::post('logs/{log}/retry', [LogController::class, 'retry'])->name('logs.retry');

    Route::get('domains/{domain}/test', [DomainController::class, 'testForm'])->name('domains.test');
    Route::post('domains/{domain}/test', [DomainController::class, 'testSend'])->name('domains.test.send');

    Route::get('tutorial', fn() => view('panel.tutorial'))->name('tutorial');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
