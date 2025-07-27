<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MonitorController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/MonitorChamadas');
    }

    // Método novo: API para retornar a última chamada + histórico
    public function dadosChamadas()
    {
        $ultimasChamadas = Chamada::with('senha')
            ->latest()
            ->take(3)
            ->get()
            ->reverse()
            ->map(function ($chamada) {
                return [
                    'senha' => $chamada->senha->codigo ?? '---',
                    'nome' => $chamada->senha->nome_paciente ?? 'Paciente',
                    'chamada_em' => $chamada->created_at->format('H:i:s'),
                ];
            });

        $chamadaAtual = $ultimasChamadas->last();

        return response()->json([
            'atual' => $chamadaAtual,
            'ultimas' => $ultimasChamadas,
        ]);
    }
}
