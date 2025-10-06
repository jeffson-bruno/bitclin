<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotProfissional
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('profissional')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
