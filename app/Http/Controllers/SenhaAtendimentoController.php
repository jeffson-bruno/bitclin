<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SenhaAtendimento;

class SenhaAtendimentoController extends Controller
{
    // Método para gerar a senha direto, com validação e retorno JSON
    public function gerarSenha(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'tipo' => 'required|in:convencional,prioridade',
        ]);

        $dataHoje = now()->toDateString();
        $prefixo = $request->tipo === 'prioridade' ? 'PR' : 'C';

        $totalHoje = SenhaAtendimento::where('tipo', $request->tipo)
            ->where('data_emissao', $dataHoje)
            ->count();

        $numero = str_pad($totalHoje + 1, 3, '0', STR_PAD_LEFT);
        $codigo = $prefixo . $numero;

        $senha = SenhaAtendimento::create([
            'paciente_id' => $request->paciente_id,
            'tipo' => $request->tipo,
            'codigo' => $codigo,
            'data_emissao' => $dataHoje,
        ]);

        return response()->json([
            'mensagem' => 'Senha gerada com sucesso!',
            'senha' => $senha,
        ]);
    }

    // Método padrão store para uso com rota POST /senhas (convenção REST)
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'tipo' => 'required|in:convencional,prioridade',
        ]);

        // Gera o código da senha usando o método privado
        $codigo = $this->gerarNumeroSenha($request->tipo);

        $senha = SenhaAtendimento::create([
            'paciente_id' => $request->paciente_id,
            'tipo' => $request->tipo,
            'codigo' => $codigo,
            'data_emissao' => now()->toDateString(),
        ]);

        return response()->json([
            'mensagem' => 'Senha gerada com sucesso!',
            'senha' => $senha,
        ]);
    }

    // Método privado para gerar número da senha com prefixo e número sequencial
    private function gerarNumeroSenha($tipo)
    {
        $prefixo = $tipo === 'prioridade' ? 'PR' : 'C';
        $dataHoje = now()->toDateString();

        // Pega a maior senha do tipo e data de hoje para incrementar
        $ultimaSenha = SenhaAtendimento::where('tipo', $tipo)
            ->where('data_emissao', $dataHoje)
            ->orderByDesc('codigo')
            ->first();

        if ($ultimaSenha) {
            // Extrai a parte numérica do código (ex: PR005 => 5)
            $numeroAtual = (int) filter_var($ultimaSenha->codigo, FILTER_SANITIZE_NUMBER_INT);
            $numeroNovo = $numeroAtual + 1;
        } else {
            $numeroNovo = 1;
        }

        return $prefixo . str_pad($numeroNovo, 3, '0', STR_PAD_LEFT);
    }

    public function imprimir($id)
    {
        $senha = SenhaAtendimento::with('paciente')->findOrFail($id);

        return view('senhas.imprimir', compact('senha'));
    }

}
