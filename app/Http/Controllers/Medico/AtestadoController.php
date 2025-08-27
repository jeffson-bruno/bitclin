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
            'texto'       => 'required|string',
            'cid'         => 'nullable|string',
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        $medico   = auth()->user();

        $registro = $this->montaRegistro($medico);

        $pdf = Pdf::loadView('pdfs.atestado', [
            'paciente' => $paciente,
            'medico'   => $medico,                 // tem ->name e ->usuario
            'registro' => $registro,               // string tipo "CRM-PI 12345"
            'texto'    => $request->texto,
            'cid'      => $request->cid,
            'data'     => now()->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->stream('atestado.pdf');
    }

    public function gerarPdf($paciente_id, $cid, $texto)
    {
        $texto    = urldecode($texto);
        $cid      = urldecode($cid);
        $paciente = Paciente::findOrFail($paciente_id);
        $medico   = auth()->user();

        $registro = $this->montaRegistro($medico);

        $pdf = Pdf::loadView('pdfs.atestado', [
            'paciente' => $paciente,
            'medico'   => $medico,
            'registro' => $registro,
            'texto'    => $texto,
            'cid'      => $cid,
            'data'     => now()->format('d/m/Y'),
        ])->setPaper('a4');

        return $pdf->stream("atestado-{$paciente->nome}.pdf");
    }

    /**
     * Monta a string do registro profissional (ex.: CRM-PI 12345)
     */
    private function montaRegistro($medico): ?string
    {
        // Se você criou o accessor getRegistroProfissionalAttribute, pode só:
        // return $medico->registro_profissional;

        $tipo = $medico->registro_tipo;
        $num  = $medico->registro_numero;
        $uf   = $medico->registro_uf ? strtoupper($medico->registro_uf) : null;

        if (!$tipo || !$num) return null;

        return $uf ? "{$tipo}-{$uf} {$num}" : "{$tipo} {$num}";
    }
}
