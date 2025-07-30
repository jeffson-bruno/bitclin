<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use Barryvdh\DomPDF\Facade\Pdf;

class ExameController extends Controller
{
    public function gerarSolicitacaoExames(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'exames' => 'required|string' // Recebido como JSON string
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        $medico = auth()->user();
        $exames = json_decode($request->exames, true);
        $dataAtual = now()->format('d/m/Y');

        $pdf = Pdf::loadView('pdfs.solicitacao-exames', [
            'paciente' => $paciente,
            'medico' => $medico,
            'exames' => $exames,
            'data' => $dataAtual,
        ])->setPaper('a4');

        return $pdf->stream('solicitacao-exames.pdf');
    }
}
