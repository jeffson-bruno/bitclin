<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Anamnese;

class AnamneseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'queixa_principal' => 'nullable|string',
            'historia_doenca' => 'nullable|string',
            'historico_medico' => 'nullable|string',
            'historico_familiar' => 'nullable|string',
            'habitos_vida' => 'nullable|string',
            'revisao_sistemas' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $validated['medico_id'] = auth()->id();

        $anamnese = Anamnese::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anamnese salva com sucesso!',
            'anamnese' => $anamnese,
        ]);
    }
}
