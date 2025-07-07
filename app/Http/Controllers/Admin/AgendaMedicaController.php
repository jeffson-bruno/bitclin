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
        $agendas = AgendaMedica::with('medico')->orderBy('data')->get();
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
    ]);

    AgendaMedica::create([
        'medico_id'   => $request->medico_id,
        'data'        => $request->dia,
        'hora_inicio' => $request->hora_inicio,
        'hora_fim'    => $request->hora_fim,
    ]);

        return redirect()->route('admin.agenda-medica.index')->with('success', 'Agenda cadastrada com sucesso!');
    }

    public function destroy($id)
    {
        $agenda = AgendaMedica::findOrFail($id);
        $agenda->delete();

        return redirect()->back()->with('success', 'Agenda removida com sucesso!');
    }
}
