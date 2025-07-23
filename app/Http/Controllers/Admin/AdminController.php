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
            ->whereDate('data_consulta', $hoje)
            ->count();
        $listaPacientesConsultaHoje = Paciente::where('procedimento', 'consulta')
            ->whereDate('data_consulta', $hoje)
            ->with(['medico.especialidade'])
            ->get();
        
        // Faturamento dos últimos 7 dias
        $agora = now();
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
            ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
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
            ->whereDate('data_consulta', $hoje) // FILTRA PELA DATA DE CONSULTA
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'data_consulta' => $p->data_consulta
                        ? Carbon::parse($p->data_consulta)->format('d/m/Y')
                        : 'Não informado',
                    'telefone' => $p->telefone,
                    'medico' => $p->medico->name ?? 'Não informado',
                    'especialidade' => $p->medico?->especialidade?->nome ?? 'Não informada',
                ];
            });

        return response()->json($pacientes);
    }

    public function pacientesExamesSemana(Request $request)
{
    $diasSemana = [
        'domingo' => 0,
        'segunda' => 1,
        'terça' => 2,
        'quarta' => 3,
        'quinta' => 4,
        'sexta' => 5,
        'sábado' => 6,
    ];

    $pacientes = Paciente::where('procedimento', 'exame')
        ->with('exame')
        ->get()
        ->map(function ($p) use ($diasSemana) {
            $dia = strtolower($p->dia_semana_exame ?? '');
            $hoje = Carbon::now()->startOfWeek(); // Segunda

            if (!isset($diasSemana[$dia])) {
                $dataConvertida = 'Não informado';
            } else {
                $dataConvertida = $hoje->copy()->addDays($diasSemana[$dia]);
                $dataConvertida = ucfirst($dataConvertida->translatedFormat('d/m/Y – l'));
            }

            return [
                'id' => $p->id,
                'nome' => $p->nome,
                'data_exame' => $dataConvertida,
                'telefone' => $p->telefone,
                'exame' => $p->exame->nome ?? 'Não informado',
            ];
        });

    return response()->json($pacientes);
}


}

