<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Especialidade;
use Inertia\Inertia;

class EspecialidadeController extends Controller
{
    public function index()
    {
        $especialidades = Especialidade::all();

        return Inertia::render('Admin/Especialidades/Index', [
            'especialidades' => $especialidades
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:especialidades,nome'
        ]);

        Especialidade::create([
            'nome' => $request->nome
        ]);

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidade criada com sucesso.');
    }

    public function update(Request $request, Especialidade $especialidade)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:especialidades,nome,' . $especialidade->id
        ]);

        $especialidade->update([
            'nome' => $request->nome
        ]);

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidade atualizada com sucesso.');
    }

    public function destroy(Especialidade $especialidade)
    {
        $especialidade->delete();

        return redirect()->route('admin.especialidades.index')->with('success', 'Especialidade excluída.');
    }
}
