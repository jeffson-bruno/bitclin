<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Despesa;
use Illuminate\Http\Request;
use Inertia\Inertia;


class DespesaController extends Controller
{
    // Listar todas as despesas
    public function index()
    {
        $despesas = Despesa::orderBy('data_pagamento')->get();

        return Inertia::render('Admin/Financeiro/Despesas', [
            'despesas' => Despesa::orderBy('data_pagamento')->get(),
        ]);
    }

    // Cadastrar nova despesa
    public function store(Request $request)
    {
        $request->validate([
            'nome'           => 'required|string|max:255',
            'valor'          => 'required|numeric',
            'data_pagamento' => 'required|date',
        ]);

        Despesa::create($request->only('nome','valor','data_pagamento'));

        // Inertia espera redirect back para recarregar props
        return back()->with('success','Despesa cadastrada com sucesso.');
    }

    // Dar baixa na despesa
    public function baixar($id)
    {
        $despesa = Despesa::findOrFail($id);
        $despesa->update(['pago' => true]);

        // Retorna resposta sem conteúdo para funcionar com Inertia/Vue
        return response()->noContent();

        
    }

    public function destroy($id)
    {
        $despesa = Despesa::findOrFail($id);
        $despesa->delete();

        return response()->noContent(); // Ou redirect()->back() se quiser recarregar manualmente
    }

}

