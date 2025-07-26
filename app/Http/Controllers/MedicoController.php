<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MedicoController extends Controller
{
    public function dashboard()
    {
        $medicoId = Auth::id();
        $hoje = Carbon::today()->toDateString();

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
                    'senha' => $p->senha ?? null, // se já tiver senha gerada
                ];
            });

        // Se for requisição AJAX (axios), retorna JSON
        //if (request()->wantsJson()) {
            //return response()->json([
                //'pacientes' => $pacientes
           // ]);
        //}

        return Inertia::render('Medico/Dashboard', [
            'pacientes' => $pacientes,
        ]);
    }
}
