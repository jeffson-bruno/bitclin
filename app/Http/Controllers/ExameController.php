<?php

namespace App\Http\Controllers;

use App\Models\Exame;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exames = Exame::select('id', 'nome', 'valor', 'created_at')->latest()->get();

        return Inertia::render('Exames/Index', [
            'exames' => $exames,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'turno' => 'required|in:manha,tarde,ambos',
            'dias_semana' => 'nullable|array',
        ]);

        Exame::create([
            'nome'        => $request->nome,
            'valor'       => $request->valor,
            'turno'       => $request->turno,
            'dias_semana' => $request->dias_semana, // ← Aqui salvamos o array no banco (como JSON)
        ]);


        return redirect()->back()->with('success', 'Exame cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exame $exame)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'turno' => 'required|in:manha,tarde,ambos',
        ]);

        $exame->update([
            'nome'        => $request->nome,
            'valor'       => $request->valor,
            'turno'       => $request->turno,
            'dias_semana' => $request->dias_semana,
        ]);


        return redirect()->back()->with('success', 'Exame atualizado com sucesso.');
    }

    //Retorna os  Dados do Exame
    public function info($id)
    {
        $exame = Exame::findOrFail($id);

        return response()->json([
            'preco'        => $exame->valor,
            'turno'        => $exame->turno,
            'dias_semana'  => $exame->dias_semana,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exame $exame)
    {
        $exame->delete();

        return redirect()->back()->with('success', 'Exame excluído com sucesso.');
    }
}
