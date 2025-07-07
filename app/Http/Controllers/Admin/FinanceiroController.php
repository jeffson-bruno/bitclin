<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class FinanceiroController extends Controller
{
    public function index()
{
    $hoje         = Carbon::today();
    $inicioSemana = Carbon::now()->startOfWeek();
    $inicioMes    = Carbon::now()->startOfMonth();

    // Totais pagos
    $entradasDia = Paciente::where('pago', true)
        ->whereDate('data_pagamento', $hoje)
        ->sum('preco');

    $entradasSemana = Paciente::where('pago', true)
        ->whereBetween('data_pagamento', [$inicioSemana, $hoje])
        ->sum('preco');

    $entradasMes = Paciente::where('pago', true)
        ->whereBetween('data_pagamento', [$inicioMes, $hoje])
        ->sum('preco');

    // Pendentes de pagamento
    //$pendentes = Paciente::where('pago', false)->get();
    $pendentes = Paciente::where('pago', false)->get()->map(function ($p) {
        $p->preco = (float) $p->preco; // converte explicitamente
        return $p;
    });
    $totalPendentes = $pendentes->sum('preco');

    // Comparativo de ganhos por tipo (Consulta x Exame)
    [$mesAtualConsulta, $mesAtualExame] = $this->totaisPorProcedimento($inicioMes, $hoje);

    $inicioMesAnterior = $inicioMes->copy()->subMonthNoOverflow();
    $fimMesAnterior    = $inicioMes->copy()->subDay();
    [$mesAntConsulta, $mesAntExame] = $this->totaisPorProcedimento($inicioMesAnterior, $fimMesAnterior);

    // Pagamentos por forma (PIX, dinheiro, cartão)
    $formasPagamento = Paciente::where('pago', true)
        ->whereBetween('data_pagamento', [$inicioMes, $hoje])
        ->selectRaw('forma_pagamento, SUM(preco) as total')
        ->groupBy('forma_pagamento')
        ->pluck('total', 'forma_pagamento');

    return Inertia::render('Admin/Financeiro/Index', [
        'resumo' => [
            'entradas_dia'    => $entradasDia,
            'entradas_semana' => $entradasSemana,
            'entradas_mes'    => $entradasMes,
        ],
        'pendentes' => $pendentes,
        'totalPendentes' => $totalPendentes,
        'grafico_comparativo' => [
            'labels'        => ['Consultas', 'Exames'],
            'mes_atual'     => [$mesAtualConsulta, $mesAtualExame],
            'mes_anterior'  => [$mesAntConsulta, $mesAntExame],
        ],
        'grafico_pagamentos' => [
            'labels'  => $formasPagamento->keys(),
            'valores' => $formasPagamento->values(),
        ],
    ]);
}


    private function totaisPorProcedimento($inicio, $fim)
    {
        $consultas = Paciente::where('pago', true)
            ->where('procedimento', 'consulta')
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->sum('preco');

        $exames = Paciente::where('pago', true)
            ->where('procedimento', 'exame')
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->sum('preco');

        return [$consultas, $exames];
    }

    public function baixarPagamento($id)
    {
        $paciente = Paciente::findOrFail($id);

        $paciente->update([
            'pago' => true,
            'data_pagamento' => now(),
        ]);

        return redirect()->back()->with('success', 'Pagamento confirmado com sucesso!');
    }

}