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

            $inicioSemana = Carbon::now()->startOfWeek(); // segunda-feira
            $fimSemana = Carbon::now()->endOfWeek(); // domingo

            $pacientes = Paciente::with('exame')
                ->where('procedimento', 'exame')
                ->whereBetween('data_exame', [$inicioSemana, $fimSemana])
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'nome' => $p->nome,
                        'data' => $p->data_exame
                            ? Carbon::parse($p->data_exame)->translatedFormat('d/m/Y – l')
                            : 'Não informada',
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
        $hoje = \Carbon\Carbon::today();

        // Consultas do dia (como já fazia)
        $consultas = \App\Models\Paciente::with('medico.especialidade')
            ->where('procedimento', 'consulta')
            ->whereDate('data_consulta', $hoje)
            ->get()
            ->map(function ($p) {
                return [
                    'tipo' => 'consulta',
                    'id' => 'C'.$p->id,
                    'nome' => $p->nome,
                    'data_consulta' => $p->data_consulta
                        ? \Carbon\Carbon::parse($p->data_consulta)->format('d/m/Y')
                        : 'Não informada',
                    'especialidade' => optional(optional($p->medico)->especialidade)->nome ?? 'Não informada',
                    'medico' => optional($p->medico)->name ?? 'Não informado',
                    'telefone' => $p->telefone,
                ];
            });

        // ➕ Retornos do dia
        $retornos = \App\Models\Retorno::with(['paciente', 'medico.especialidade'])
            ->whereDate('data_retorno', $hoje)
            ->get()
            ->map(function ($r) {
                return [
                    'tipo' => 'retorno',
                    'id' => 'R'.$r->id,
                    'nome' => $r->paciente->nome,
                    // usa o MESMO campo 'data_consulta' p/ front reaproveitar a tabela sem mudar nada
                    'data_consulta' => $r->data_retorno
                        ? \Carbon\Carbon::parse($r->data_retorno)->format('d/m/Y')
                        : 'Não informada',
                    'especialidade' => optional(optional($r->medico)->especialidade)->nome ?? 'Não informada',
                    'medico' => optional($r->medico)->name ?? 'Não informado',
                    'telefone' => $r->paciente->telefone,
                ];
            });

        // Junta e retorna
        $lista = $consultas->concat($retornos)->values();

        return response()->json($lista);
    } catch (\Throwable $e) {
        \Log::error('Erro ao buscar pacientesConsultaHoje: '.$e->getMessage());
        return response()->json(['error' => 'Erro interno ao buscar pacientes'], 500);
    }
}



}




