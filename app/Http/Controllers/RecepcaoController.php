<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AgendaMedica;
use Carbon\Carbon;


class RecepcaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Recepcao/Dashboard');
    }
    

    public function horariosMedicos(Request $request)
    {
        $data = $request->query('data'); // Ex: 2025-07-20

        if (!$data) {
            return response()->json(['error' => 'Data não informada'], 400);
        }

        $horarios = AgendaMedica::with('medico.especialidade')
            ->whereDate('data', $data)
            ->get()
            ->map(function ($agenda) {
                return [
                    'medico' => $agenda->medico->name,
                    'especialidade' => $agenda->medico->especialidade->nome ?? 'N/D',
                    'hora_inicio' => Carbon::parse($agenda->hora_inicio)->format('H:i'),
                    'hora_fim' => Carbon::parse($agenda->hora_fim)->format('H:i'),
                ];
            });

        return response()->json($horarios);
    }
}

