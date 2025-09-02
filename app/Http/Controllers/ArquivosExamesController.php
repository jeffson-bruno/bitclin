<?php

namespace App\Http\Controllers;

use App\Models\ExameArquivo;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArquivosExamesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'arquivos'    => ['required', 'array', 'min:1'],
            'arquivos.*'  => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
        ]);

        $pacienteId = (int) $request->paciente_id;
        $userId     = (int) auth()->id();

        $salvos = [];

        // USE $file (do foreach), não $request->file('arquivo')
        foreach ($request->file('arquivos', []) as $file) {
            $path = $file->storePublicly("pacientes/{$pacienteId}/exames", 'public');

            $registro = ExameArquivo::create([
                'paciente_id'   => $pacienteId,
                'uploaded_by'   => $userId,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'size_bytes'    => $file->getSize(),
                'path'          => $path,
            ]);

            $salvos[] = $this->toResource($registro);
        }

        return response()->json(['ok' => true, 'itens' => $salvos]);
    }

    public function indexPaciente(Paciente $paciente)
    {
        $arquivos = ExameArquivo::with('uploader:id,name')
            ->where('paciente_id', $paciente->id)
            ->latest()
            ->get()
            ->map(fn ($a) => $this->toResource($a));

        return response()->json(['ok' => true, 'arquivos' => $arquivos]);
    }

    public function download(ExameArquivo $arquivo): StreamedResponse
    {
        $this->authorizeVerArquivo($arquivo);
        return Storage::disk('public')->download($arquivo->path, $arquivo->original_name);
    }

    public function inline(ExameArquivo $arquivo)
    {
        $this->authorizeVerArquivo($arquivo);

        if (!Storage::disk('public')->exists($arquivo->path)) {
            abort(404);
        }

        $content = Storage::disk('public')->get($arquivo->path);

        return response($content, 200, [
            'Content-Type'        => $arquivo->mime_type,
            'Content-Disposition' => 'inline; filename="'.$arquivo->original_name.'"',
        ]);
    }

    public function destroy(ExameArquivo $arquivo)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'receptionist'])) {
            abort(403);
        }
        Storage::disk('public')->delete($arquivo->path);
        $arquivo->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeVerArquivo(ExameArquivo $arquivo): void
    {
        if (!auth()->check()) abort(401);
        if (!auth()->user()->hasAnyRole(['admin', 'receptionist', 'doctor', 'enfermeiro'])) {
            abort(403);
        }
    }

    // Helper para padronizar payload ao front (evita depender de accessors)
    private function toResource(ExameArquivo $a): array
    {
        return [
            'id'          => $a->id,
            'nome'        => $a->original_name,
            'mime'        => $a->mime_type,
            'tamanho'     => $a->size_bytes,
            'url'         => Storage::disk('public')->url($a->path),                
            'inline_url'  => route('exames-arquivos.inline', $a->id),
            'download_url'=> route('exames-arquivos.download', $a->id),
            'uploaded_by' => $a->uploader?->name,
            'created_at'  => optional($a->created_at)->format('Y-m-d H:i'),
        ];
    }

}
