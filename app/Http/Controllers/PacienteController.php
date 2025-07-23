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
    public function index(Request $request)
    {
        $pacientes = Paciente::orderBy('created_at', 'desc')->paginate(10);
        $medicos = User::where('role', 'doctor')->select('id', 'name')->get();
        $exames = Exame::select('id', 'nome')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $pacientes->items(),
                'pagination' => [
                    'current_page' => $pacientes->currentPage(),
                    'last_page' => $pacientes->lastPage(),
                    'total' => $pacientes->total(),
                ]
            ]);
        }

        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes,
            'medicos' => $medicos,
            'exames' => $exames
        ]);
    }

    public function create()
    {
        return Inertia::render('Pacientes/Create');
    }

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
            'data_nascimento' => 'required|string',
            'data_consulta' => 'nullable|string', // ← corrigido aqui
            'turno_exame' => 'nullable|in:manha,tarde,ambos',
            'dia_semana_exame' => 'nullable|string|max:20',
        ]);

        if ($validated['pago'] && empty($validated['data_pagamento'])) {
            $validated['data_pagamento'] = now();
        }

        // Conversão manual de data_consulta se vier como dd/mm/yyyy
        if (!empty($validated['data_consulta'])) {
            if (str_contains($validated['data_consulta'], '/')) {
                $partes = explode('/', $validated['data_consulta']);
                if (count($partes) === 3) {
                    $validated['data_consulta'] = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
                }
            }
        }

        // Conversão manual de data_nascimento se vier como dd/mm/yyyy
        if (!empty($validated['data_nascimento'])) {
            if (str_contains($validated['data_nascimento'], '/')) {
                $data = explode('/', $validated['data_nascimento']);
                if (count($data) === 3) {
                    $validated['data_nascimento'] = "{$data[2]}-{$data[1]}-{$data[0]}";
                }
            }
        }

        if ($validated['procedimento'] === 'exame' && !empty($validated['exame_id'])) {
            // Se já existe em $validated, não mexe
            if (isset($validated['dia_semana_exame']) && $validated['dia_semana_exame']) {
                // já está pronto, não precisa fazer nada
            } else {
                // fallback: tenta pegar o primeiro dia do exame
                $exame = Exame::find($validated['exame_id']);
                if ($exame && $exame->dias_semana) {
                    $dias = is_array($exame->dias_semana) ? $exame->dias_semana : json_decode($exame->dias_semana, true);
                    $validated['dia_semana_exame'] = is_array($dias) ? $dias[0] ?? null : null;
                }
            }
        }




        Paciente::create($validated);

        return redirect()->route('pacientes.index')->with('success', 'Paciente cadastrado com sucesso!');
    }

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
            'dia_semana_exame' => 'nullable|string|max:20',
        ]);

        $paciente->update($validated);

        return response()->json(['message' => 'Paciente atualizado com sucesso!']);
    }

    public function reagendar(Request $request, $id)
    {
        $request->validate([
            'data_consulta' => 'nullable|date',
            'exame_id' => 'nullable|exists:exames,id',
            'medico_id' => 'nullable|exists:users,id',
            'procedimento' => 'required|in:consulta,exame',
        ]);

        $paciente = Paciente::findOrFail($id);

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

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')->with('success', 'Paciente deletado com sucesso!');
    }
}
