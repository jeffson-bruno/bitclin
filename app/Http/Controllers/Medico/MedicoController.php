<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MedicoController extends Controller
{
    public function index()
    {
        // Obter o ID do médico logado
        $medicoId = Auth::id();

        // Obter a data atual (sem hora)
        $hoje = Carbon::today()->toDateString();

        // Buscar pacientes com consulta para hoje e médico correspondente
        $pacientes = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->orderBy('data_consulta')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'idade' => Carbon::parse($p->data_nascimento)->age,
                    'estado_civil' => $p->estado_civil,
                    'data' => $p->data_consulta,
                    'telefone' => $p->telefone,
                    'senha' => $p->senha ?? null,
                ];
            });

        return Inertia::render('Medico/Dashboard', [
            'pacientes' => $pacientes,
        ]);
    }
}