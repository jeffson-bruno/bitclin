<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AnamneseController extends Controller
{
    public function __construct(){ $this->middleware(['auth','role:enfermeiro']); }

    public function store(Request $request)
    {
        // Validação básica
        $data = $request->validate([
            'paciente_id'        => ['required', 'exists:pacientes,id'],
            'medico_id'          => ['nullable', 'integer'],
            'pressao_arterial'   => ['nullable', 'string', 'max:20'],

            'queixa_principal'   => ['nullable', 'string', 'max:5000'],
            'historia_doenca'    => ['nullable', 'string', 'max:10000'],
            'historia_doença'    => ['nullable', 'string', 'max:10000'],
            'historico_medico'   => ['nullable', 'string', 'max:10000'],
            'historico_familiar' => ['nullable', 'string', 'max:10000'],
            'habitos_vida'       => ['nullable', 'string', 'max:10000'],
            'revisao_sistemas'   => ['nullable', 'string', 'max:10000'],
            'observacoes'        => ['nullable', 'string', 'max:10000'],
            'conteudo'           => ['nullable', 'string', 'max:10000'], // compat
        ]);

        try {
            $table = 'anamneses';
            if (!Schema::hasTable($table)) {
                return $this->respond($request, false, 'Tabela "anamneses" não encontrada.');
            }

            // Tenta obter medico_id (se não foi enviado)
            $medicoId = $data['medico_id'] ?? null;
            if (Schema::hasColumn($table, 'medico_id') && !$medicoId) {
                $medicoId = $this->resolveMedicoId((int) $data['paciente_id']);
            }

            $payload = [
                'paciente_id' => $data['paciente_id'],
                'origem'      => 'enfermeiro',
                'user_id'     => auth()->id(),
            ];

            if (Schema::hasColumn($table, 'medico_id')) {
                $payload['medico_id'] = $medicoId; // pode continuar null se a coluna for nullable
            }

            if (Schema::hasColumn($table, 'pressao_arterial') && !empty($data['pressao_arterial'])) {
                $payload['pressao_arterial'] = $data['pressao_arterial'];
            }

            // Map de campos textuais
            $textMap = [
                'queixa_principal'   => 'queixa_principal',
                'historia_doenca'    => Schema::hasColumn($table, 'historia_doenca') ? 'historia_doenca' :
                                         (Schema::hasColumn($table, 'historia_doença') ? 'historia_doença' : null),
                'historico_medico'   => 'historico_medico',
                'historico_familiar' => 'historico_familiar',
                'habitos_vida'       => 'habitos_vida',
                'revisao_sistemas'   => 'revisao_sistemas',
                'observacoes'        => 'observacoes',
            ];

            foreach ($textMap as $reqKey => $colName) {
                if ($colName && Schema::hasColumn($table, $colName)) {
                    $val = $request->input($reqKey);
                    if ($val !== null && $val !== '') {
                        $payload[$colName] = $val;
                    }
                }
            }

            // compat: se veio "conteudo" e não veio "observacoes"
            if (
                (!isset($payload['observacoes']) || $payload['observacoes'] === null)
                && !empty($data['conteudo'])
                && Schema::hasColumn($table, 'observacoes')
            ) {
                $payload['observacoes'] = $data['conteudo'];
            }

            // timestamps
            $now = now();
            if (Schema::hasColumn($table, 'created_at')) $payload['created_at'] = $now;
            if (Schema::hasColumn($table, 'updated_at')) $payload['updated_at'] = $now;

            DB::table($table)->insert($payload);

            return $this->respond($request, true, 'Anamnese (triagem) registrada com sucesso.', [
                'saved' => $payload
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar anamnese (enfermeiro): '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Em dev, ajude a identificar: se APP_DEBUG=true, devolve causa
            $msg = config('app.debug')
                ? 'Falha ao salvar: '.$e->getMessage()
                : 'Falha ao salvar a anamnese da enfermagem.';

            return $this->respond($request, false, $msg);
        }
    }

    /** Tenta descobrir o médico associado ao paciente em colunas comuns da tabela `pacientes`. */
    private function resolveMedicoId(int $pacienteId): ?int
    {
        if (!Schema::hasTable('pacientes')) return null;

        $fkCandidates = ['medico_id','doctor_id','id_medico','profissional_id','user_id'];
        $cols = array_filter($fkCandidates, fn($c) => Schema::hasColumn('pacientes', $c));
        if (empty($cols)) return null;

        $row = DB::table('pacientes')
            ->select($cols)
            ->where('id', $pacienteId)
            ->first();

        if (!$row) return null;

        foreach ($cols as $c) {
            if (!empty($row->{$c})) return (int) $row->{$c};
        }
        return null;
    }

    private function respond(Request $request, bool $ok, string $msg, array $extra = [])
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(array_merge(['ok' => $ok, 'message' => $msg], $extra));
        }
        return back()->with($ok ? 'success' : 'error', $msg);
    }
}