<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Paciente;

class AtestadoController extends Controller
{
    public function gerarAtestado(Request $request)
{
    $request->validate([
        'paciente_id' => 'required|exists:pacientes,id',
        'texto' => 'required|string',
        'cid' => 'nullable|string',
    ]);

    $paciente = \App\Models\Paciente::findOrFail($request->paciente_id);
    $medico = auth()->user();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.atestado', [
        'paciente' => $paciente,
        'medico' => $medico,
        'texto' => $request->texto,
        'cid' => $request->cid,
        'data' => now()->format('d/m/Y'),
    ])->setPaper('a4');

    // Retorna o PDF diretamente no navegador
    return $pdf->stream('atestado.pdf');
}

public function gerarPdf($paciente_id, $cid, $texto)
{
    $texto = urldecode($texto);
    $cid = urldecode($cid);

    $paciente = \App\Models\Paciente::findOrFail($paciente_id);
    $medico = auth()->user();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.atestado', [
        'paciente' => $paciente,
        'medico' => $medico,
        'texto' => $texto,
        'cid' => $cid,
        'data' => now()->format('d/m/Y'),
    ])->setPaper('a4');

    return $pdf->stream("atestado-{$paciente->nome}.pdf");
}


}

