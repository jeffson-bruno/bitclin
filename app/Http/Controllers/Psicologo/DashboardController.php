<?php

namespace App\Http\Controllers\Psicologo;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct() { $this->middleware(['auth','role:psicologo']); }

    public function index()
    {
        return inertia('Psicologo/Dashboard', [
            // props iniciais se precisar
        ]);
    }
}
