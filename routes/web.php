<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use App\Http\Controllers\Panel\AccountController;
use App\Http\Controllers\Panel\DomainController;
use App\Http\Controllers\Panel\LogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('panel.domains.index');
    }
    // Belum ada admin sama sekali -> arahkan ke setup awal.
    return redirect()->route(User::exists() ? 'login' : 'register');
});

Route::middleware(['guest', 'panel.enabled'])->group(function () {
    // Setup awal: hanya bisa diakses selama belum ada satu pun akun admin.
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->name('register.post')
        ->middleware('throttle:5,1');

    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.post')
        ->middleware('throttle:3,1'); // max (total) attempt per 1 menit per IP
});

Route::middleware(['auth', 'panel.enabled'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', fn() => redirect()->route('panel.domains.index'));
    // Endpoint AJAX DataTables (harus di atas resource agar tak tertangkap {domain})
    Route::get('domains/data', [DomainController::class, 'data'])->name('domains.data');
    Route::resource('domains', DomainController::class);

    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('logs/data', [LogController::class, 'data'])->name('logs.data');
    Route::delete('logs/prune', [LogController::class, 'prune'])->name('logs.prune');
    Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::post('logs/{log}/retry', [LogController::class, 'retry'])->name('logs.retry');

    Route::get('domains/{domain}/test', [DomainController::class, 'testForm'])->name('domains.test');
    Route::post('domains/{domain}/test', [DomainController::class, 'testSend'])->name('domains.test.send');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    Route::get('tutorial', fn() => view('panel.tutorial'))->name('tutorial');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
