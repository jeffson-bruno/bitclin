<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use Inertia\Inertia;

class AtendimentoController extends Controller
{
    public function atender(Paciente $paciente)
    {
        $medico = auth()->user(); // opcional

        return Inertia::render('Medico/AtendimentoPaciente', [
            'paciente' => $paciente,
            'medico' => $medico,
        ]);
    }
}

