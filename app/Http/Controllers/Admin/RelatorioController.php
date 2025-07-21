<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Despesa;
use App\Models\Paciente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function relatorioDia()
    {
        $hoje = Carbon::today();

        $entradas = Paciente::whereDate('created_at', $hoje)->get();
        $despesas = Despesa::whereDate('data_pagamento', $hoje)->get();

        return $this->gerarPdf('pdfs.relatorios.periodo', $entradas, $despesas, 'dia', $hoje, $hoje);
    }

    public function relatorioSemana()
    {
        $inicio = Carbon::now()->startOfWeek();
        $fim = Carbon::now()->endOfWeek();

        $entradas = Paciente::whereBetween('data_pagamento', [$inicio, $fim])->get();

        $despesas = Despesa::whereBetween('data_pagamento', [$inicio, $fim])->get();

        return $this->gerarPdf('pdfs.relatorios.periodo', $entradas, $despesas, 'semana', $inicio, $fim);
    }

    public function relatorioMes()
    {
        $inicio = Carbon::now()->startOfMonth();
        $fim = Carbon::now()->endOfMonth();

        $entradas = Paciente::whereBetween('data_pagamento', [$inicio, $fim])->get();

        $despesas = Despesa::whereBetween('data_pagamento', [$inicio, $fim])->get();

        return $this->gerarPdf('pdfs.relatorios.periodo', $entradas, $despesas, 'mês', $inicio, $fim);
    }

    public function relatorioAnual()
    {
        $ano = Carbon::now()->year;
        $relatorioMensal = [];

        $maiorLucro = null;
        $mesMaisLucro = null;

        $maiorGasto = null;
        $mesMaisGasto = null;

        for ($mes = 1; $mes <= 12; $mes++) {
            $inicio = Carbon::create($ano, $mes, 1)->startOfMonth();
            $fim = Carbon::create($ano, $mes, 1)->endOfMonth();

            $entradas = Paciente::whereBetween('data_pagamento', [$inicio, $fim])->get();

            $despesas = Despesa::whereBetween('data_pagamento', [$inicio, $fim])->get();

            $totalEntradas = array_sum(array_column($relatorioMensal, 'entradas'));
            $totalDespesas = array_sum(array_column($relatorioMensal, 'despesas'));
            $saldoAnual = $totalEntradas - $totalDespesas;

            $nomeMes = ucfirst($inicio->locale('pt_BR')->translatedFormat('F'));

            $saldo = $totalEntradas - $totalDespesas;

            $relatorioMensal[$nomeMes] = [
                'entradas' => $totalEntradas,
                'despesas' => $totalDespesas,
                'saldo' => $saldo,
            ];

            if (is_null($maiorLucro) || $saldo > $maiorLucro) {
                $maiorLucro = $saldo;
                $mesMaisLucro = $nomeMes;
            }

            if (is_null($maiorGasto) || $totalDespesas > $maiorGasto) {
                $maiorGasto = $totalDespesas;
                $mesMaisGasto = $nomeMes;
            }
        }

        return Pdf::loadView('pdfs.relatorios.anual', [
        'relatorioMensal' => $relatorioMensal,
        'ano' => $ano,
        'mesMaisLucro' => $mesMaisLucro,
        'mesMaisGasto' => $mesMaisGasto,
        'totalEntradas' => $totalEntradas,
        'totalDespesas' => $totalDespesas,
        'saldoAnual' => $saldoAnual,
    ])
    ->setPaper('a4')
    ->download("relatorio_anual_{$ano}.pdf");
    }

    private function gerarPdf($view, $entradas, $despesas, $periodo, $data_inicio, $data_fim)
    {
        $totalEntradas = $entradas->sum('preco');
        $totalDespesas = $despesas->sum('valor');
        $saldo = $totalEntradas - $totalDespesas;

        $pdf = Pdf::loadView($view, [
            'entradas' => $entradas,
            'despesas' => $despesas,
            'totalEntradas' => $totalEntradas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $saldo,
            'periodo' => $periodo,
            'data_inicio' => $data_inicio->format('d/m/Y'),
            'data_fim' => $data_fim->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->download('relatorio-' . Str::slug($periodo) . '.pdf');
    }
}
