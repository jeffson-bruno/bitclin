<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicoController extends Controller
{
    public function index()
    {
        return Inertia::render('Medico/Dashboard', [
            'title' => 'Painel do Médico'
        ]);
    }
}
