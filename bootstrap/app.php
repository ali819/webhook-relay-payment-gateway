<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'panel.enabled' => \App\Http\Middleware\PanelEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->is('login')) {
                Log::warning('Login throttled', [
                    'ip'         => $request->ip(),
                    'email'      => $request->input('email'),
                    'user_agent' => $request->userAgent(),
                    'time'       => now()->toDateTimeString(),
                ]);

                return redirect()->route('login')
                    ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi beberapa saat.']);
            }
        });
    })->create();
