<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Prontuario;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProntuarioController extends Controller
{
    // Salva um novo prontuário (usado ao finalizar o atendimento)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_id' => 'required|exists:users,id',
            'queixa_principal' => 'nullable|string',
            'historia_doenca' => 'nullable|string',
            'historico_progressivo' => 'nullable|string',
            'historico_familiar' => 'nullable|string',
            'habitos_vida' => 'nullable|string',
            'revisao_sistemas' => 'nullable|string',
            'receitas' => 'nullable|array',
            'atestados' => 'nullable|array',
            'exames' => 'nullable|array',
        ]);

        Prontuario::create($validated);

        return response()->json(['success' => true, 'message' => 'Prontuário salvo com sucesso!']);
    }

    // Visualiza o prontuário do paciente
    public function visualizar(Paciente $paciente)
    {
        $prontuarios = Prontuario::where('paciente_id', $paciente->id)->with('medico')->latest()->get();

        return Inertia::render('Medico/ProntuarioPaciente', [
            'paciente' => $paciente,
            'prontuarios' => $prontuarios,
        ]);
    }
}
