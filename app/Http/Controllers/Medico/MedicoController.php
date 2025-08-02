<?php

namespace App\Http\Controllers\Medico;

use App\Models\User;
use App\Models\Receita;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Chamada;
use App\Models\SenhaAtendimento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;



class MedicoController extends Controller
{
    public function index()
    {
        // Obter o ID do médico logado
        $medicoId = Auth::id();

        // Obter a data atual (sem hora)
        $hoje = Carbon::today()->toDateString();

        // Buscar pacientes com consulta para hoje e médico correspondente
        $agendados = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->where('foi_atendido', false)
            ->orderBy('data_consulta')
            ->get();

        $atendidos = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->where('foi_atendido', true)
            ->orderBy('data_consulta')
            ->get();

        return Inertia::render('Medico/Dashboard', [
            'pacientesAgendados' => $agendados,
            'pacientesAtendidos' => $atendidos,
        ]);

        
    }

    public function chamarSenha($senhaId)
    {
        $medicoId = Auth::id();

        // Conta quantas vezes o médico já chamou essa senha
        $tentativas = Chamada::where('senha_id', $senhaId)
                            ->where('medico_id', $medicoId)
                            ->count();

        if ($tentativas >= 3) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Limite de chamadas atingido para essa senha.'
            ], 403);
        }

        // Registra nova chamada
        Chamada::create([
            'senha_id' => $senhaId,
            'medico_id' => $medicoId,
            'tentativa' => $tentativas + 1,
        ]);

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Senha chamada com sucesso!',
            'tentativas_restantes' => 2 - $tentativas,
        ]);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function gerarReceita(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'crm' => 'required|string',
            'medicamentos' => 'required|array',
        ]);

        $medico = auth()->user();
        $paciente = Paciente::findOrFail($request->paciente_id);
        $medicamentos = $request->medicamentos;

        // Salva apenas os dados (sem salvar o PDF)
    Receita::create([
        'paciente_id' => $paciente->id,
        'medico_id' => $medico->id,
        'crm' => $request->crm,
        'conteudo' => json_encode($medicamentos),
        'data_receita' => now(),
    ]);

        // Gera e retorna o PDF diretamente
        $pdf = Pdf::loadView('pdfs.receita', [
            'paciente' => $paciente,
            'medico' => $medico,
            'medicamentos' => $medicamentos,
            'crm' => $request->crm,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('receita.pdf');
    }



    public function finalizarAtendimento(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        $paciente->foi_atendido = true;
        $paciente->status_atendimento = 'atendido'; // use se for necessário também
        $paciente->save();

        return response()->json(['success' => true]);
    }




    ///////////////////////////////////////////////////////////////////////////////////////////

}