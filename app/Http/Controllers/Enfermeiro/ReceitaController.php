<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Receita;
use App\Models\Prontuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceitaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:enfermeiro']);
    }

    public function gerarReceita(Request $request)
    {
        $request->validate([
            'paciente_id'                 => ['required','exists:pacientes,id'],
            'medicamentos'                => ['required','array','min:1'],
            'medicamentos.*.nome'         => ['required','string'],
            'medicamentos.*.posologia'    => ['nullable','string','max:1000'],
        ]);

        $enfermeiro = auth()->user();
        $paciente   = Paciente::findOrFail($request->paciente_id);

        // Normaliza itens
        $itens = collect($request->medicamentos)
            ->map(function ($m) {
                $nome = trim((string)($m['nome'] ?? ''));
                $poso = trim((string)($m['posologia'] ?? ''));
                if ($nome === '') return null;
                return ['nome' => $nome, 'posologia' => $poso !== '' ? $poso : null];
            })
            ->filter()->values()->all();

        if (empty($itens)) {
            return response()->json(['message' => 'Adicione pelo menos um medicamento.'], 422);
        }

        // Registro profissional (ex.: COREN-PI 12345)
        $registro = $this->montaRegistro($enfermeiro);

        DB::beginTransaction();
        try {
            // 1) Salva a receita (emissor = enfermeiro)
            Receita::create([
                'paciente_id'  => $paciente->id,
                'medico_id'    => $enfermeiro->id,   // emissor (pode ser enfermeiro)
                'crm'          => $registro,         // texto do registro (COREN/CRP/…)
                'conteudo'     => $itens,            // array (model deve ter cast)
                'data_receita' => now(),
                // se tiver coluna 'origem' em receitas, pode salvar:
                // 'origem' => 'enfermeiro',
            ]);

            // 2) Anexa ao PRONTUÁRIO do dia (sem quebrar o do médico)
            $hoje = now()->toDateString();

            $prontuario = Prontuario::firstOrCreate(
                ['paciente_id' => $paciente->id, 'data_atendimento' => $hoje],
                [
                    // tenta usar médico do paciente; se não existir, usa o próprio enfermeiro
                    'medico_id' => $this->resolveMedicoId($paciente) ?? $enfermeiro->id,
                ]
            );

            // garante cast para array
            $existentes = is_array($prontuario->receitas ?? null) ? $prontuario->receitas : [];

            $entrada = array_map(function ($m) use ($enfermeiro) {
                return [
                    'nome'        => $m['nome'],
                    'posologia'   => $m['posologia'] ?? null,
                    'emissor'     => 'enfermeiro',
                    'emissor_id'  => $enfermeiro->id,
                    'created_at'  => now()->toDateTimeString(),
                ];
            }, $itens);

            $prontuario->receitas = array_values(array_merge($existentes, $entrada));
            $prontuario->save();

            DB::commit();

            // 3) Gera PDF específico da Enfermagem
            $pdf = Pdf::loadView('pdfs.receita_enfermeiro', [
                'paciente'     => $paciente,
                'enfermeiro'   => $enfermeiro,
                'registro'     => $registro,
                'medicamentos' => $itens,
                'data'         => now()->format('d/m/Y'),
            ])->setPaper('A4','portrait');

            return $pdf->stream('receita_enfermagem.pdf');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Falha ao gerar receita da enfermagem.'], 500);
        }
    }

    private function montaRegistro($user): ?string
    {
        $tipo = $user->registro_tipo;     // COREN / CRP / etc.
        $num  = $user->registro_numero;
        $uf   = $user->registro_uf ? strtoupper($user->registro_uf) : null;

        if (!$tipo || !$num) return null;
        return $uf ? "{$tipo}-{$uf} {$num}" : "{$tipo} {$num}";
    }

    private function resolveMedicoId(Paciente $paciente): ?int
    {
        // tenta as colunas mais comuns sem quebrar se não existirem
        foreach (['medico_id','doctor_id','id_medico','profissional_id','user_id'] as $c) {
            if (Schema::hasColumn('pacientes', $c) && !empty($paciente->{$c})) {
                return (int) $paciente->{$c};
            }
        }
        return null;
    }
}
