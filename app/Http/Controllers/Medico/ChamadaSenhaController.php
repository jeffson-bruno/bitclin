<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SenhaAtendimento;
use Illuminate\Support\Carbon;
use App\Models\Chamada;
use Illuminate\Support\Facades\Auth;




class ChamadaSenhaController extends Controller
{
    //
    public function chamar($paciente_id)
    {
        $medicoId = Auth::id();

        // Corrigido: o campo correto deve ser paciente_id, sem "__"
        $senha = SenhaAtendimento::where('paciente_id', $paciente_id)->latest()->first();

        if (!$senha) {
            return response()->json(['error' => 'Senha não encontrada.'], 404);
        }

        // Verifica quantas vezes esse médico já chamou essa senha
        $tentativas = Chamada::where('senha_id', $senha->id)
                            ->where('medico_id', $medicoId)
                            ->count();

        if ($tentativas >= 3) {
            return response()->json(['erro' => 'Limite de chamadas atingido.'], 403);
        }

        // Registra a nova tentativa
        Chamada::create([
            'senha_id' => $senha->id,
            'medico_id' => $medicoId,
            'tentativa' => $tentativas + 1,
        ]);

        // Marca como chamada (opcional)
        $senha->chamada_em = now();
        $senha->save();

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Senha chamada com sucesso.',
            'dados' => [
                'senha' => $senha->codigo,
                'nome_paciente' => $senha->nome_paciente ?? 'Paciente',
                'tentativas_restantes' => 2 - $tentativas,
            ]
        ]);
    }
}
