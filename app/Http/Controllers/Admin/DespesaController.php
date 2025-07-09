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
            'despesas' => $despesas,
        ]);
    }

    // Cadastrar nova despesa
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'data_pagamento' => 'required|date',
        ]);

        Despesa::create([
            'nome' => $request->nome,
            'valor' => $request->valor,
            'data_pagamento' => $request->data_pagamento,
            'pago' => false,
        ]);

        return redirect()->back()->with('success', 'Despesa cadastrada com sucesso!');
    }

    // Dar baixa na despesa
    public function baixar($id)
    {
        $despesa = Despesa::findOrFail($id);

        $despesa->update([
            'pago' => true,
            'data_pagamento' => now(),
        ]);

        return redirect()->back()->with('success', 'Despesa baixada com sucesso!');
    }
}

