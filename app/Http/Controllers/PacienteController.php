<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    // Exibe a lista de pacientes (mais para frente podemos alterar para retornar via Inertia)
    public function index()
    {
        //$pacientes = Paciente::paginate(10);  // Paginando a lista de pacientes com 10 por página
        $pacientes = Paciente::orderBy('created_at', 'desc')->paginate(10);

    // Retorna a resposta com o componente Vue e envia os pacientes como dados
        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes
    ]);
    }
    

    // Exibe o formulário de criação (Create.vue)
    public function create()
    {
        return Inertia::render('Pacientes/Create');
    }

    // Armazena um novo paciente no banco
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date',
            'cpf' => 'nullable|string|max:11',
            'endereco' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'procedimento' => 'nullable|string',
            'preco' => 'nullable|numeric',
            'pago' => 'nullable|boolean',
            'forma_pagamento' => 'nullable|string',
            'data_pagamento' => 'nullable|date',
        ]);

        $paciente = Paciente::create($request->all());

        return redirect()->route('pacientes.index')->with('success', 'Paciente cadastrado com sucesso!');

    }

    // Exibe detalhes de um paciente específico
    public function show(Paciente $paciente)
    {
        return response()->json($paciente);
    }

    // Atualiza um paciente existente
    public function update(Request $request, Paciente $paciente)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:15',
            'data_nascimento' => 'nullable|date',
            'cpf' => 'nullable|string|max:14',
            'endereco' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'procedimento' => 'nullable|string',
            'preco' => 'nullable|numeric',
            'pago' => 'nullable|boolean',
            'forma_pagamento' => 'nullable|string',
            'data_pagamento' => 'nullable|date',
        ]);

        $paciente->update($request->all());

        return response()->json($paciente);
    }

    // Deleta um paciente
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return response()->json(null, 204);
    }
}
