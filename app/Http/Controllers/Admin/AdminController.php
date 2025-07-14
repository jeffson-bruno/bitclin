<?php

namespace App\Http\Controllers\Admin;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Despesa;
use App\Models\AgendaMedica;
use Illuminate\Support\Carbon;

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
            ->pluck('medico.name') // pegar só o nome
            ->unique()
            ->values();

        // Pacientes para consulta cadastrados hoje
        $pacientesConsultaHoje = Paciente::where('procedimento', 'consulta')
            ->whereDate('created_at', $hoje)
            ->count();

        return inertia('Admin/Dashboard', [
            'title' => 'Painel do Administrador',
            //'consultasHoje' => $consultasHoje,
            'pacientesTotal' => $pacientesTotal,
            //'consultasNoMes' => $consultasNoMes,
            'despesasHoje' => $despesasHoje,
            'medicosHoje' => $medicosHoje,
            'pacientesConsultaHoje' => $pacientesConsultaHoje,
        ]);
    }
}

