<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct(){ $this->middleware(['auth','role:enfermeiro']); }

    public function index()
    {
        return inertia('Enfermeiro/Dashboard', [
            'pacientesAgendados' => [], // front chama JSON de /agendados-hoje
            'pacientesAtendidos' => [],
        ]);
    }
}
