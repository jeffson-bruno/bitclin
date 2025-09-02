<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Verifica o papel do usuário logado
    $user = auth()->user();

    // redireciono por papel
    if ($user->hasRole('admin')) {
        return redirect()->intended(route('admin.dashboard'));
    }

    if ($user->hasRole('receptionist')) {
        return redirect()->route('recepcao.dashboard');
    }

    if ($user->hasRole('enfermeiro')){
        return redirect()->intended(route('enfermeiro.dashboard'));
    }   

    if ($user->hasRole('psicologo')) {
        return redirect()->intended(route('psicologo.dashboard'));
    }  

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
