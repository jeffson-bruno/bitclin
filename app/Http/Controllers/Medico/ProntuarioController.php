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

        $dataAtendimento = now()->toDateString(); // yyyy-mm-dd

        // Procura um prontuário existente para o mesmo paciente e data
        $prontuario = Prontuario::where('paciente_id', $validated['paciente_id'])
            ->where('data_atendimento', $dataAtendimento)
            ->first();

        if ($prontuario) {
            // Atualiza apenas os campos que vieram preenchidos
            foreach ($validated as $campo => $valor) {
                if (in_array($campo, ['receitas', 'atestados', 'exames']) && $valor) {
                    // Adiciona aos existentes (mescla arrays)
                    $existente = $prontuario->$campo ?? [];
                    $prontuario->$campo = array_merge($existente, $valor);
                } elseif (!is_array($valor) && !is_null($valor)) {
                    // Atualiza apenas se veio preenchido
                    $prontuario->$campo = $valor;
                }
            }

            $prontuario->save();
        } else {
            // Cria um novo prontuário com data do atendimento
            $validated['data_atendimento'] = $dataAtendimento;
            $prontuario = Prontuario::create($validated);
        }

        return response()->json(['success' => true, 'message' => 'Prontuário atualizado com sucesso!']);
    }

    // Visualiza o prontuário do paciente
    public function visualizar(Paciente $paciente)
    {
        $prontuarios = Prontuario::where('paciente_id', $paciente->id)
            ->with('medico')
            ->orderBy('data_atendimento', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->data_atendimento)->format('d/m/Y');
            });

        return Inertia::render('Medico/ProntuarioPaciente', [
            'paciente' => $paciente,
            'prontuariosPorData' => $prontuarios,
        ]);
    }

}
