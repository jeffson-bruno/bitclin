<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encaminhamento;

class EncaminhamentoController extends Controller
{
    public function __construct(){ $this->middleware(['auth','role:enfermeiro']); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id'          => ['required','exists:pacientes,id'],
            'para_medico_id'       => ['nullable','exists:users,id'],
            'para_especialidade_id'=> ['nullable','exists:especialidades,id'],
            'observacoes'          => ['nullable','string','max:2000'],
        ]);

        // regra do seu negócio: só permitir encaminhar se o agendamento foi para o enfermeiro
        // você pode validar isso aqui, consultando o agendamento do dia.

        Encaminhamento::create([
            'paciente_id'           => $data['paciente_id'],
            'de_user_id'            => auth()->id(),
            'para_medico_id'        => $data['para_medico_id'] ?? null,
            'para_especialidade_id' => $data['para_especialidade_id'] ?? null,
            'observacoes'           => $data['observacoes'] ?? null,
        ]);

        return back()->with('success','Encaminhamento registrado.');
    }
}
