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
            'exames'      => 'required|array|min:1',
            'exames.*'    => 'string|required'
        ]);

        $paciente  = \App\Models\Paciente::findOrFail($request->paciente_id);
        $medico    = auth()->user();
        $exames    = $request->input('exames');
        $registro  = $this->montaRegistro($medico);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.solicitacao-exames', [
            'paciente' => $paciente,
            'medico'   => $medico,
            'exames'   => $exames,
            'registro' => $registro,
            'data'     => now()->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->stream('solicitacao-exames.pdf');
    }

    public function gerarPdf($paciente_id, $exames)
    {
        $paciente = \App\Models\Paciente::findOrFail($paciente_id);
        $medico   = auth()->user();

        $listaExames = explode(',', urldecode($exames));
        $registro    = $this->montaRegistro($medico);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.solicitacao-exames', [
            'paciente' => $paciente,
            'medico'   => $medico,
            'exames'   => $listaExames,
            'registro' => $registro,
            'data'     => now()->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->stream("solicitacao-exames-{$paciente->nome}.pdf");
    }

    /** copie o helper pro controller também */
    private function montaRegistro($medico): ?string
    {
        $tipo = $medico->registro_tipo;
        $num  = $medico->registro_numero;
        $uf   = $medico->registro_uf ? strtoupper($medico->registro_uf) : null;

        if (!$tipo || !$num) return null;
        return $uf ? "{$tipo}-{$uf} {$num}" : "{$tipo} {$num}";
    }

}
