<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class AtendimentoController extends Controller
{
    public function __construct(){ $this->middleware(['auth','role:enfermeiro']); }

    public function index($pacienteId)
    {
        $paciente = Paciente::select('id','nome','cpf','data_nascimento')->findOrFail($pacienteId);

        return inertia('Enfermeiro/AtendimentoPaciente', [
            'paciente' => $paciente,
            // esta flag será usada no painel do médico para mostrar a aba "Triagem"
            // (o próprio painel do médico pode calcular também)
        ]);
    }
}
