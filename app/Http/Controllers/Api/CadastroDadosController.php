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
            ->map(function ($p) use ($abreviacoes, $diasSemana) {
                $sigla = strtoupper($p->dia_semana_exame ?? '');
                $diaExtenso = $abreviacoes[$sigla] ?? null;

                if (!$diaExtenso || !isset($diasSemana[$diaExtenso])) {
                    $dataExame = 'Não informado';
                } else {
                    $hoje = now()->startOfWeek(Carbon::MONDAY);
                    $dataExame = $hoje->copy()->addDays($diasSemana[$diaExtenso])
                        ->translatedFormat('d/m/Y – l');
                }

                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'data_exame' => $dataExame,
                    'telefone' => $p->telefone,
                    'exame' => optional($p->exame)->nome,
                ];
            });

        return response()->json($pacientes);
    }

}


