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
        $pacientes = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->orderBy('data_consulta')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'data_nascimento' => $p->data_nascimento,
                    'idade' => Carbon::parse($p->data_nascimento)->age,
                    'estado_civil' => $p->estado_civil,
                    'data' => $p->data_consulta,
                    'telefone' => $p->telefone,
                    'senha' => $p->senha ?? null,
                ];
            });

        return Inertia::render('Medico/Dashboard', [
            'pacientes' => $pacientes,
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
        $medicoId = Auth::id(); // Pega o ID do médico logado

        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'crm' => 'required|string',
            'medicamentos' => 'required|array',
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        $medico = \App\Models\User::findOrFail($medicoId); // Usa o médico logado
        $medicamentos = $request->medicamentos;

        $data = now()->format('Y-m-d_H-i-s');
        $fileName = 'receita_' . $paciente->id . '_' . $data . '.pdf';

        $crm = $request->crm;

        $pdf = Pdf::loadView('pdfs.receita', compact('paciente', 'medico', 'medicamentos', 'crm'))
            ->setPaper('A4', 'portrait');

        Storage::disk('public')->put('receitas/' . $fileName, $pdf->output());

        $receita = Receita::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'arquivo' => 'receitas/' . $fileName,
            'crm' => $request->crm,
            'data_receita' => now()
        ]);


        return response()->json([
            'success' => true,
            'receita_id' => $receita->id,
            'url' => asset('storage/receitas/' . $fileName),
        ]);
    }


    ///////////////////////////////////////////////////////////////////////////////////////////

}