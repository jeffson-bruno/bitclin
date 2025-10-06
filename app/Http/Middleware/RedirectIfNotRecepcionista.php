<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotRecepcionista
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('recepcionista')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
