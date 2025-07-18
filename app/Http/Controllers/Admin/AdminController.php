<?php

namespace App\Http\Controllers\Admin;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Despesa;
use App\Models\AgendaMedica;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Http\Controllers\Controller;
use Inertia\Inertia;



class AdminController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();

        //$consultasHoje = Consulta::whereDate('data', $hoje)->count();
        $pacientesTotal = Paciente::count();

        //$consultasNoMes = Consulta::whereMonth('data', $hoje->month)
                                  //->whereYear('data', $hoje->year)
                                  //->count();
        // Buscar despesas com vencimento HOJE e ainda NÃO PAGAS
        $despesasHoje = Despesa::whereDate('data_pagamento', $hoje)
            ->where('pago', false)
            ->get(['id', 'nome', 'valor']);

        // Buscar os médicos que têm consulta HOJE na agenda médica
        $medicosHoje = AgendaMedica::whereDate('data', $hoje)
            ->with('medico')
            ->get()
            ->map(function ($agenda) {
                return [
                    'nome' => $agenda->medico->name ?? 'Não informado',
                    'hora_inicio' => $agenda->hora_inicio,
                    'hora_fim' => $agenda->hora_fim,
                ];
            });
        // Definir início e fim da semana
        $inicioSemana = Carbon::now()->startOfWeek(); // segunda-feira
        $fimSemana = Carbon::now()->endOfWeek(); // domingo

        $examesSemana = Paciente::where('procedimento', 'exame')
            ->whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->count();

        // Pacientes para consulta cadastrados hoje
        $pacientesConsultaHoje = Paciente::where('procedimento', 'consulta')
            ->whereDate('created_at', $hoje)
            ->count();
        $listaPacientesConsultaHoje = Paciente::where('procedimento', 'consulta')
            ->whereDate('created_at', $hoje)
            ->with(['medico.especialidade'])
            ->get();
        
        // Faturamento dos últimos 7 dias
        $hoje = now();
        $seteDiasAtras = now()->subDays(6);

        $faturamentoUltimos7Dias = DB::table('pacientes')
            ->select(DB::raw('DATE(created_at) as data'), DB::raw('SUM(preco) as total'))
            ->where('pago', 1)
            ->whereBetween(DB::raw('DATE(created_at)'), [$seteDiasAtras, $hoje])
            ->where('procedimento', 'consulta') // ou incluir exames se quiser
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('data')
            ->get();

        // Lucro vs Despesas do mês atual
        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();

        $entradas = DB::table('pacientes')
            ->where('pago', 1)
            ->whereBetween('created_at', [$inicioMes, $fimMes])
            ->sum('preco');

        $despesas = DB::table('despesas')
            ->where('pago', 1)
            ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
            ->sum('valor');


        return inertia('Admin/Dashboard', [
            'title' => 'Painel do Administrador',
            //'consultasHoje' => $consultasHoje,
            'pacientesTotal' => $pacientesTotal,
            //'consultasNoMes' => $consultasNoMes,
            'despesasHoje' => $despesasHoje,
            'medicosHoje' => $medicosHoje,
            'pacientesConsultaHoje' => $pacientesConsultaHoje,
            'pacientesConsultaHojeList' => $listaPacientesConsultaHoje,
            'examesSemana' => $examesSemana,
            'faturamentoDias' => $faturamentoUltimos7Dias,
                'lucroVsDespesas' => [
                    'entradas' => $entradas,
                    'despesas' => $despesas,
                ],

        ]);
    }
    public function pacientesConsultaHoje(Request $request)
    {
        $hoje = Carbon::today()->toDateString();

        $pacientes = Paciente::with(['medico.especialidade'])
            ->where('procedimento', 'consulta')
            ->whereDate('created_at', $hoje)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'data_consulta' => $p->created_at->format('d/m/Y H:i'),
                    'telefone' => $p->telefone,
                    'medico' => $p->medico->name ?? 'Não informado',
                    'especialidade' => $p->medico?->especialidade?->nome ?? 'Não informada',
                ];
            });

        return response()->json($pacientes);
    }
    public function pacientesExamesSemana(Request $request)
    {
        $inicioSemana = Carbon::now()->startOfWeek(); // Segunda
        $fimSemana = Carbon::now()->endOfWeek();     // Domingo

        $pacientes = Paciente::where('procedimento', 'exame')
            ->whereBetween('created_at', [$inicioSemana, $fimSemana])
            ->with('exame') // Assumindo que há relação com tabela de exames
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'data_exame' => $p->created_at->format('d/m/Y H:i'),
                    'telefone' => $p->telefone,
                    'exame' => $p->exame->nome ?? 'Não informado',
                ];
            });

        return response()->json($pacientes);
    }

}

