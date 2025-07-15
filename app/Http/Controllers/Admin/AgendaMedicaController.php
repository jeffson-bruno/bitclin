<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgendaMedica;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;


class AgendaMedicaController extends Controller
{
    public function index()
    {
        //$agendas = AgendaMedica::with('medico')->orderBy('data')->get();
        $agendas = AgendaMedica::with('medico')->orderBy('data')->get()->map(function ($agenda) {
            return [
                'id' => $agenda->id,
                'data' => $agenda->data,
                'hora_inicio' => $agenda->hora_inicio,
                'hora_fim' => $agenda->hora_fim,
                'preco_consulta' => (float) $agenda->preco_consulta, // força conversão para número
                'medico' => $agenda->medico ? ['name' => $agenda->medico->name] : null,
            ]; 
        });
        $medicos = User::role('doctor')->get(['id', 'name']);

        return Inertia::render('Admin/AgendaMedica/Index', [
            'agendas' => $agendas,
            'medicos' => $medicos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
        'medico_id'    => 'required|exists:users,id',
        'dia'          => 'required|date',
        'hora_inicio'  => 'required',
        'hora_fim'     => 'required',
        'preco_consulta' => 'required|numeric|min:0',
    ]);

    AgendaMedica::create([
        'medico_id'   => $request->medico_id,
        'data'        => $request->dia,
        'hora_inicio' => $request->hora_inicio,
        'hora_fim'    => $request->hora_fim,
        'preco_consulta' => $request->preco_consulta,
    ]);

        return redirect()->route('admin.agenda-medica.index')->with('success', 'Agenda cadastrada com sucesso!');
    }

    public function buscarPreco($id)
    {
        $agenda = \App\Models\AgendaMedica::where('medico_id', $id)
            ->orderByDesc('data')
            ->first();

        return response()->json([
            'preco_consulta' => $agenda?->preco_consulta ?? 0,
        ]);
    }


    public function destroy($id)
    {
        $agenda = AgendaMedica::findOrFail($id);
        $agenda->delete();

        return redirect()->back()->with('success', 'Agenda removida com sucesso!');
    }
}
