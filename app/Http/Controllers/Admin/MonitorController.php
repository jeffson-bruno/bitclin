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
        $chamadaAtual = Chamada::with('senha.paciente')->latest()->first();

        $ultimasChamadas = Chamada::with('senha')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'atual' => [
                'senha' => $chamadaAtual->senha->codigo ?? '',
                'nome' => $chamadaAtual->senha->paciente->nome ?? '', // <- aqui é a correção
            ],
            'ultimas' => $ultimasChamadas->map(function ($c) {
                return [
                    'senha' => $c->senha->codigo ?? '',
                ];
            }),
        ]);
    }

}
