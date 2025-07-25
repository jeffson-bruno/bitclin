<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgendaMedica;
use App\Models\Paciente;
use Illuminate\Support\Carbon;

class CadastroDadosController extends Controller
{
    /**
     * Buscar dias disponíveis na agenda de um médico.
     */
    public function diasDisponiveisConsulta($medicoId)
    {
        $dias = AgendaMedica::where('medico_id', $medicoId)
            ->orderBy('data')
            ->pluck('data')
            ->map(fn($data) => Carbon::parse($data)->format('d/m/Y'));

        return response()->json($dias);
    }

    /**
     * Buscar o preço da consulta com o médico.
     */
    public function precoConsulta($medicoId)
    {
        $agenda = \App\Models\AgendaMedica::where('medico_id', $medicoId)
            ->orderBy('data', 'desc')
            ->first();

        return response()->json([
            'preco' => $agenda->preco_consulta ?? 0,
        ]);
    }


    /**
     * Buscar dados de exame: valor, turno e dias disponíveis.
     */
    public function infoExame($id)
    {
        $exame = \App\Models\Exame::findOrFail($id);

        return response()->json([
            'preco' => $exame->valor,
            'turno' => $exame->turno,
            'dias_semana' => json_decode($exame->dias_semana ?? '[]'),
        ]);
    }

    /**
     * Reagendar paciente (consulta ou exame).
     */
    public function reagendar(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        if ($request->procedimento === 'consulta') {
            $paciente->medico_id = $request->medico_id;
            $paciente->data_consulta = $request->data_consulta;
        } elseif ($request->procedimento === 'exame') {
            $paciente->exame_id = $request->exame_id;
            $paciente->turno_exame = $request->turno_exame;
            $paciente->dia_semana_exame = $request->dia_semana_exame;
        }

        $paciente->procedimento = $request->procedimento;
        $paciente->save();

        return response()->json(['message' => 'Paciente reagendado com sucesso!']);
    }

    public function pacientesExamesSemana()
    {
        try {
            Carbon::setLocale('pt_BR');

            // Map com chaves em minúsculo
            $diasMap = [
                'domingo' => Carbon::SUNDAY,
                'segunda' => Carbon::MONDAY,
                'terça'   => Carbon::TUESDAY,
                'quarta'  => Carbon::WEDNESDAY,
                'quinta'  => Carbon::THURSDAY,
                'sexta'   => Carbon::FRIDAY,
                'sabado'  => Carbon::SATURDAY,
            ];

            $pacientes = Paciente::with('exame')
                ->where('procedimento', 'exame')
                ->get()
                ->map(function ($p) use ($diasMap) {
                    $diaSemana = strtolower(trim($p->dia_semana_exame ?? ''));

                    if (!isset($diasMap[$diaSemana])) {
                        $dataFormatada = 'Não informada';
                    } else {
                        $hoje = Carbon::today();
                        $carbonDia = Carbon::now()->next($diasMap[$diaSemana]);

                        // Garante que seja a próxima ocorrência se o dia for hoje
                        if ($carbonDia->lt($hoje)) {
                            $carbonDia->addWeek();
                        }

                        $dataFormatada = $carbonDia->translatedFormat('d/m/Y – l');
                    }

                    return [
                        'id' => $p->id,
                        'nome' => $p->nome,
                        'dia_semana_exame' => ucfirst($diaSemana),
                        'data' => $dataFormatada,
                        'exame' => optional($p->exame)->nome ?? 'Não informado',
                        'turno' => ucfirst($p->turno_exame) ?? '-',
                        'telefone' => $p->telefone,
                    ];
                });

            return response()->json($pacientes);
        } catch (\Throwable $e) {
            \Log::error('Erro ao buscar exames da semana: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar exames'], 500);
        }
    }

    public function pacientesConsultaHoje()
    {
        try {
            $hoje = Carbon::today()->toDateString();

            $pacientes = Paciente::with('medico.especialidade')
                ->where('procedimento', 'consulta')
                ->whereDate('data_consulta', $hoje)
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'nome' => $p->nome,
                        'data_consulta' => $p->data_consulta ? Carbon::parse($p->data_consulta)->format('d/m/Y') : 'Não informada',
                        'especialidade' => optional($p->medico->especialidade)->nome ?? 'Não informada',
                        'medico' => optional($p->medico)->name ?? 'Não informado',
                        'telefone' => $p->telefone,
                    ];
                });

            return response()->json($pacientes);
        } catch (\Throwable $e) {
            \Log::error('Erro ao buscar pacientesConsultaHoje: '.$e->getMessage());
            return response()->json(['error' => 'Erro interno ao buscar pacientes'], 500);
        }
    }



}




