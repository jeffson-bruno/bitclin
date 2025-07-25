<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AgendaMedica;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Paciente;
use Illuminate\Support\Str;
use App\Models\Exame;

class RecepcaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Recepcao/Dashboard');
    }

    public function horariosMedicos(Request $request)
    {
        $data = $request->query('data');

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
                    'display' => 'background',
                    'className' => 'evento-consulta-dia',
                ];
            });

        $agendamentos = Paciente::with(['medico', 'exame'])
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
                'allDay' => true,
            ];
        });

        return response()->json(['consultas' => $consultas]);
    }

    public function agendamentosDaSemana()
    {
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $fimSemana = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(5);

        $agendamentos = Paciente::with(['medico', 'exame'])
            ->where(function ($query) use ($inicioSemana, $fimSemana) {
                $query->where(function ($q) use ($inicioSemana, $fimSemana) {
                    $q->where('procedimento', 'consulta')
                        ->whereBetween('data_consulta', [$inicioSemana->startOfDay(), $fimSemana->endOfDay()]);
                })->orWhere(function ($q) {
                    $q->where('procedimento', 'exame')
                        ->whereNotNull('dia_semana_exame');
                });
            })
            ->orderByRaw("COALESCE(data_consulta, created_at)")
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'paciente' => $p->nome,
                    'tipo' => $p->procedimento === 'consulta' ? 'Consulta' : 'Exame',
                    'data' => $p->procedimento === 'consulta'
                        ? ($p->data_consulta ?? $p->created_at->format('Y-m-d'))
                        : (function () use ($p) {
                            $diasSemana = [
                                'domingo' => 0,
                                'segunda' => 1,
                                'terça' => 2,
                                'quarta' => 3,
                                'quinta' => 4,
                                'sexta' => 5,
                                'sábado' => 6,
                            ];
                            $dia = Str::lower($p->dia_semana_exame);
                            $hoje = now()->startOfWeek(Carbon::MONDAY);

                            return isset($diasSemana[$dia])
                                ? $hoje->copy()->addDays($diasSemana[$dia])->format('Y-m-d')
                                : $p->created_at->format('Y-m-d');
                        })(),
                    'medico' => optional($p->medico)->name,
                    'exame' => optional($p->exame)->nome,
                ];
            });

        return response()->json($agendamentos);
    }

    public function pacientes()
    {
        return Inertia::render('Recepcao/Pacientes', [
            'pacientes' => Paciente::orderBy('created_at', 'desc')->paginate(10),
            'medicos' => User::where('role', 'doctor')->get(['id', 'name']),
            'exames' => Exame::all(['id', 'nome', 'valor', 'turno', 'dias_semana']),
        ]);
    }

    public function buscarDiasConsulta($medicoId)
    {
        $dias = AgendaMedica::where('medico_id', $medicoId)
            ->orderBy('data')
            ->pluck('data')
            ->map(function ($data) {
                return Carbon::parse($data)->format('d/m/Y');
            });

        return response()->json($dias);
    }

    public function infoExame($id)
    {
        $exame = Exame::findOrFail($id);

        return response()->json([
            'preco' => $exame->valor,
            'turno' => $exame->turno,
            'dias_semana' => json_decode($exame->dias_semana ?? '[]'),
        ]);
    }

    // ✅ NOVA FUNÇÃO: exames da semana com base no campo dia_semana_exame
    public function pacientesExamesSemana()
    {
        Carbon::setLocale('pt_BR');

        $abreviacoes = [
            'DOM' => 'domingo',
            'SEG' => 'segunda',
            'TER' => 'terça',
            'QUAR' => 'quarta',
            'QUI' => 'quinta',
            'SEX' => 'sexta',
            'SAB' => 'sábado',
        ];

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
            ->map(function ($p) use ($diasSemana, $abreviacoes) {
                $diaAbreviado = strtoupper($p->dia_semana_exame ?? '');
                $nomeDia = $abreviacoes[$diaAbreviado] ?? strtolower($p->dia_semana_exame ?? '');

                $inicioSemana = Carbon::now()->startOfWeek(Carbon::SUNDAY);

                if (!isset($diasSemana[$nomeDia])) {
                    $dataConvertida = 'Não informado';
                } else {
                    $dataConvertida = $inicioSemana->copy()->addDays($diasSemana[$nomeDia]);
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
