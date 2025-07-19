<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AgendaMedica;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Paciente;


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

    public function consultas()
    {
        return Inertia::render('Recepcao/Consultas');
    }

    public function consultasEAgendamentos()
    {
        $consultas = AgendaMedica::with('medico')
            ->whereDate('data', '>=', now()->toDateString())
            ->get()
            ->map(function ($agenda) {
                return [
                    'title' => 'Dr. ' . explode(' ', $agenda->medico->name)[0],
                    'start' => $agenda->data,
                    'allDay' => true,
                    'color' => '#fde68a',
                    'display' => 'background', // destaques no fundo
                    'className' => 'evento-consulta-dia',
                ];
            });

        $agendamentos = \App\Models\Paciente::with(['medico', 'exame'])
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'paciente' => $p->nome,
                    'tipo' => $p->procedimento === 'consulta' ? 'Consulta' : 'Exame',
                    'data' => $p->procedimento === 'consulta' ? ($p->data_consulta ?? $p->created_at->format('Y-m-d')) : $p->created_at->format('Y-m-d'),
                    'medico' => optional($p->medico)->name,
                    'exame' => optional($p->exame)->nome,
                ];
            });

        return response()->json([
            'consultas' => $consultas,
            'agendamentos' => $agendamentos,
        ]);
    }


    public function consultasEAvisos(Request $request)
    {
        $agendas = AgendaMedica::with('medico')
        ->whereDate('data', '>=', now()->toDateString())
        ->get();

        $consultas = $agendas->map(function ($agenda) {
            $primeiroNome = explode(' ', $agenda->medico->name)[0];
            return [
                'title' => 'Dr. ' . $primeiroNome,
                'start' => $agenda->data . 'T' . $agenda->hora_inicio,
                'allDay' => true, // garante que fique no topo do quadrado
            ];
        });

        return response()->json([
            'consultas' => $consultas
        ]);
    }

    public function agendamentosDaSemana()
    {
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY); // Segunda
        $fimSemana = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(5); // Sábado

        $agendamentos = \App\Models\Paciente::with(['medico', 'exame'])
            ->whereBetween('created_at', [$inicioSemana->startOfDay(), $fimSemana->endOfDay()])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'paciente' => $p->nome,
                    'tipo' => $p->procedimento === 'consulta' ? 'Consulta' : 'Exame',
                    'data' => $p->procedimento === 'consulta'
                        ? ($p->data_consulta ?? $p->created_at->format('Y-m-d'))
                        : $p->created_at->format('Y-m-d'),
                    'medico' => optional($p->medico)->name,
                    'exame' => optional($p->exame)->nome,
                ];
            });

        return response()->json($agendamentos);
    }
}

