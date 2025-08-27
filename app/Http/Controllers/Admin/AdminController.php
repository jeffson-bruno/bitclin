<?php

namespace App\Http\Controllers\Admin;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Despesa;
use App\Models\AgendaMedica;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Models\Retorno;



use App\Http\Controllers\Controller;
use Inertia\Inertia;



class AdminController extends Controller
{
    public function index()
{
    $hoje = \Carbon\Carbon::today();

    $pacientesTotal = \App\Models\Paciente::count();

    // Despesas vencendo hoje (não pagas)
    $despesasHoje = \App\Models\Despesa::whereDate('data_pagamento', $hoje)
        ->where('pago', false)
        ->get(['id', 'nome', 'valor']);

    // Médicos com consulta HOJE na agenda
    $medicosHoje = \App\Models\AgendaMedica::whereDate('data', $hoje)
        ->with('medico')
        ->get()
        ->map(function ($agenda) {
            return [
                'nome'        => $agenda->medico->name ?? 'Não informado',
                'hora_inicio' => $agenda->hora_inicio,
                'hora_fim'    => $agenda->hora_fim,
            ];
        });

    // Exames na semana
    $inicioSemana = \Carbon\Carbon::now()->startOfWeek(); // segunda
    $fimSemana    = \Carbon\Carbon::now()->endOfWeek();   // domingo

    $examesSemana = \App\Models\Paciente::where('procedimento', 'exame')
        ->whereBetween('data_exame', [$inicioSemana, $fimSemana])
        ->count();

    // =================== AQUI ESTAVA O PROBLEMA ===================
    // Consultas hoje
    $consultasHojeCount = \App\Models\Paciente::where('procedimento', 'consulta')
        ->whereDate('data_consulta', $hoje)
        ->count();

    // Retornos hoje
    $retornosHojeCount = \App\Models\Retorno::whereDate('data_retorno', $hoje)->count();

    // Total para o card (consultas + retornos)
    $pacientesConsultaHoje = $consultasHojeCount + $retornosHojeCount;
    // ==============================================================

    // (Opcional) Só mantenha essas listas se você realmente usa no front
    $listaPacientesConsultaHoje = \App\Models\Paciente::where('procedimento', 'consulta')
        ->whereDate('data_consulta', $hoje)
        ->with(['medico.especialidade'])
        ->get();

    $listaRetornosHoje = \App\Models\Retorno::with(['paciente','medico.especialidade'])
        ->whereDate('data_retorno', $hoje)
        ->get();

    // Faturamento últimos 7 dias
    $seteDiasAtras = now()->subDays(6);
    $faturamentoUltimos7Dias = \Illuminate\Support\Facades\DB::table('pacientes')
        ->select(\Illuminate\Support\Facades\DB::raw('DATE(created_at) as data'), \Illuminate\Support\Facades\DB::raw('SUM(preco) as total'))
        ->where('pago', 1)
        ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'), [$seteDiasAtras, $hoje])
        ->where('procedimento', 'consulta') // inclua 'exame' se quiser somar ambos
        ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(created_at)'))
        ->orderBy('data')
        ->get();

    // Lucro vs Despesas (mês)
    $inicioMes = now()->startOfMonth();
    $fimMes    = now()->endOfMonth();

    $entradas = \Illuminate\Support\Facades\DB::table('pacientes')
        ->where('pago', 1)
        ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
        ->sum('preco');

    $despesas = \Illuminate\Support\Facades\DB::table('despesas')
        ->where('pago', 1)
        ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
        ->sum('valor');

    return inertia('Admin/Dashboard', [
        'title'                 => 'Painel do Administrador',
        'pacientesTotal'        => $pacientesTotal,
        'despesasHoje'          => $despesasHoje,
        'medicosHoje'           => $medicosHoje,
        'pacientesConsultaHoje' => $pacientesConsultaHoje, // ✅ agora é a soma
        'pacientesConsultaHojeList' => $listaPacientesConsultaHoje, // (veja nota abaixo)
        'examesSemana'          => $examesSemana,
        'faturamentoDias'       => $faturamentoUltimos7Dias,
        'lucroVsDespesas'       => [
            'entradas' => $entradas,
            'despesas' => $despesas,
        ],
    ]);
}

    
    

}

