<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SenhaAtendimento;
use Illuminate\Support\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChamadaSenhaController extends Controller
{
    //
    public function chamar($paciente_id)
    {
        $senha = SenhaAtendimento::where('paciente__id', $paciente_id)->latest()->first();

        if (!$senha) {
            return response()->json(['error' => 'Senha não encontrada.'], 404);
        }

        $senha->chamada_em = Carbon::now();
        $senha->save();

        return response()->json(['success' => true, 'mensagem' => 'Senha chamada com sucesso!']);
    }
}
