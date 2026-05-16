<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PanelEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.panel_enabled')) {
            return response()->view('panel.disabled', [], 503);
        }

        return $next($request);
    }
}
