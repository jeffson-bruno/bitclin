<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    // Exibe a lista de pacientes (mais para frente podemos alterar para retornar via Inertia)
    public function index(Request $request)
    {
        //$pacientes = Paciente::paginate(10);  // Paginando a lista de pacientes com 10 por página
        $pacientes = Paciente::orderBy('created_at', 'desc')->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
            'data' => $pacientes->items(),  // só os dados paginados
            'pagination' => [
                'current_page' => $pacientes->currentPage(),
                'last_page' => $pacientes->lastPage(),
                'total' => $pacientes->total(),
            ]
        ]);
}

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
    $validated = $request->validate([
        'nome' => 'required|string|max:255',
        'telefone' => 'nullable|string|max:14',
        'cpf' => 'nullable|string|max:14',
        'estado_civil' => 'required|string',
        'endereco' => 'nullable|string|max:255',
        'procedimento' => 'required|string',
        'preco' => 'nullable|numeric',
        'pago' => 'required|boolean',
        'forma_pagamento' => 'nullable|string',
        'data_pagamento' => 'nullable|date',
        'data_nascimento' => 'required|string', // ← Temporário, pois vamos converter manualmente
    ]);

    // Se estiver pago e a data não foi preenchida, usar data atual
    if ($validated['pago'] && empty($validated['data_pagamento'])) {
        $validated['data_pagamento'] = now();
    }
    // Transforma data_nascimento de dd/mm/yyyy para yyyy-mm-dd
    if (!empty($validated['data_nascimento'])) {
        $data = explode('/', $validated['data_nascimento']);
        if (count($data) === 3) {
            $validated['data_nascimento'] = "{$data[2]}-{$data[1]}-{$data[0]}";
        }
    }

    // Agora usamos os dados validados e modificados
    Paciente::create($validated);

    return redirect()->route('pacientes.index')->with('success', 'Paciente cadastrado com sucesso!');
}


    // Exibe detalhes de um paciente específico
    public function show(Paciente $paciente)
    {
        return response()->json($paciente);
    }

        public function update(Request $request, $id)
        {
            $paciente = Paciente::findOrFail($id);

            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|max:14',
                'telefone' => 'required|string|max:15',
                'data_nascimento' => 'nullable|date',
                'endereco' => 'nullable|string|max:255',
                'estado_civil' => 'nullable|string|max:50',
                'procedimento' => 'required|string|max:255',
                'preco' => 'nullable|numeric',
                'pago' => 'required|boolean',
                'forma_pagamento' => 'nullable|required_if:pago,true|string|max:255',
                'data_pagamento' => 'nullable|required_if:pago,true|date',
            ]);


            $paciente->update($validated);

            return response()->json(['message' => 'Paciente atualizado com sucesso!']);
        }


    // Deleta um paciente
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')->with('success', 'Paciente deletado com sucesso!');
    }
}
