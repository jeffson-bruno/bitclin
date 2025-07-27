<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Chamada;

class MonitorController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/MonitorChamadas');
    }

    // Método novo: API para retornar a última chamada + histórico
    public function dadosChamadas()
    {
        $chamadaAtual = Chamada::with('senha')->latest()->first();

        $ultimasChamadas = Chamada::with('senha')
            ->latest()
            ->take(5)
            ->skip(1)
            ->get();

        return response()->json([
            'atual' => [
                'senha' => $chamadaAtual->senha->codigo ?? '',
                'nome' => $chamadaAtual->senha->nome ?? '',
            ],
            'ultimas' => $ultimasChamadas->map(function ($chamada) {
                return [
                    'senha' => $chamada->senha->codigo ?? '',
                    'nome' => $chamada->senha->nome ?? '',
                ];
            }),
        ]);
    }
}
