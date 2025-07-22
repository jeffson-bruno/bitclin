<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Paciente;
use App\Models\Exame;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PacienteController extends Controller
{
   

    // Exibe a lista de pacientes (mais para frente podemos alterar para retornar via Inertia)
    public function index(Request $request)
    {
        //$pacientes = Paciente::paginate(10);  // Paginando a lista de pacientes com 10 por página
        $pacientes = Paciente::orderBy('created_at', 'desc')->paginate(10);

        $medicos = User::where('role', 'doctor')->select('id', 'name')->get();
        $exames = Exame::select('id', 'nome')->get();

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
            'pacientes' => $pacientes,
            'medicos' => $medicos,
            'exames' => $exames
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
            'estado_civil' => 'required|string|max:50',
            'endereco' => 'nullable|string|max:255',
            'procedimento' => 'required|string',
            'medico_id' => 'nullable|exists:users,id',
            'exame_id' => 'nullable|exists:exames,id',
            'preco' => 'nullable|numeric',
            'pago' => 'required|boolean',
            'forma_pagamento' => 'nullable|string',
            'data_pagamento' => 'nullable|date',
            'data_nascimento' => 'required|string', // ← Temporário, pois vamos converter manualmente
            'data_consulta' => 'nullable|date',
            'turno_exame' => 'nullable|in:manha,tarde,ambos',
            'dia_semana_exame' => 'nullable|string',
        ]);

        // Se estiver pago e a data não foi preenchida, usar data atual
        if ($validated['pago'] && empty($validated['data_pagamento'])) {
            $validated['data_pagamento'] = now();
        }

        // Tratamento de data_consulta para salvar como Y-m-d
        if (!empty($validated['data_consulta'])) {
            $validated['data_consulta'] = Carbon::parse($validated['data_consulta'])->format('Y-m-d');
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
                'turno_exame' => 'nullable|in:manha,tarde,ambos',
                'dia_semana_exame' => 'nullable|string',
            ]);


            $paciente->update($validated);

            return response()->json(['message' => 'Paciente atualizado com sucesso!']);
        }
    // Reagendar um paciente
    public function reagendar(Request $request, $id)
    {
        $request->validate([
            'data_consulta' => 'nullable|date',
            'exame_id' => 'nullable|exists:exames,id',
            'medico_id' => 'nullable|exists:users,id',
            'procedimento' => 'required|in:consulta,exame',
        ]);

        $paciente = Paciente::findOrFail($id);

        // Atualiza o procedimento
        $paciente->procedimento = $request->procedimento;

        if ($request->procedimento === 'consulta') {
            $paciente->data_consulta = $request->data_consulta
                ? Carbon::parse($request->data_consulta)->format('Y-m-d')
                : null;
            $paciente->medico_id = $request->medico_id;
            $paciente->exame_id = null;
        }

        if ($request->procedimento === 'exame') {
            $paciente->exame_id = $request->exame_id;
            $paciente->data_consulta = null;
            $paciente->medico_id = null;
        }

        $paciente->save();

        return response()->json(['success' => true, 'mensagem' => 'Paciente reagendado com sucesso!']);
    }


    // Deleta um paciente
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')->with('success', 'Paciente deletado com sucesso!');
    }
}
