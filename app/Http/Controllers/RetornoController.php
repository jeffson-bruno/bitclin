<?php

namespace App\Http\Controllers;

use App\Models\Retorno;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Controllers\Api\CadastroDadosController;

class RetornoController extends Controller
{
    /**
     * Agendar retorno – sem hora específica (00:00:00).
     * Retorna redirect (Inertia-friendly) no sucesso e ValidationException no erro.
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'medico_id'   => ['required', 'exists:users,id'],
            'data'        => ['required', 'date'], // yyyy-mm-dd
            'motivo'      => ['nullable', 'string', 'max:160'],
            'observacoes' => ['nullable', 'string'],
        ]);

        // Validação extra no backend: a data precisa existir na agenda do médico
        if (!$this->dataExisteNaAgenda((int) $dados['medico_id'], (string) $dados['data'])) {
            throw ValidationException::withMessages([
                'data' => 'Só é permitido agendar retorno quando tiver próxima data de consulta na agenda do médico.',
            ]);
        }

        $dataRetorno = "{$dados['data']} 00:00:00";

        $retorno = Retorno::create([
            'paciente_id'  => $dados['paciente_id'],
            'medico_id'    => $dados['medico_id'] ?? Auth::id(),
            'data_retorno' => $dataRetorno,
            'motivo'       => $dados['motivo'] ?? null,
            'observacoes'  => $dados['observacoes'] ?? null,
            'status'       => 'agendado',
        ]);

        // Para Inertia: sempre redirecione após POST (evita “plain JSON”)
        return back()->with('success', 'Retorno agendado com sucesso.');
    }

    /**
     * Histórico/lista de retornos de um paciente.
     */
    public function porPaciente(Paciente $paciente)
    {
        return $paciente->retornos()
            ->with('medico:id,name')
            ->orderBy('data_retorno', 'desc')
            ->get();
    }

    /**
     * Encontra o médico associado ao paciente (do próprio registro
     * ou o último atendimento com médico encontrado pelo CPF).
     */
    public function medicoDoPaciente(Paciente $paciente)
    {
        $medicoId = $paciente->medico_id;

        if (!$medicoId) {
            $cpf = preg_replace('/\D+/', '', (string) $paciente->cpf);
            if ($cpf) {
                $ultimo = Paciente::query()
                    ->where('cpf', $cpf)
                    ->whereNotNull('medico_id')
                    ->orderByDesc('data_consulta')
                    ->orderByDesc('created_at')
                    ->first();
                $medicoId = $ultimo?->medico_id;
            }
        }

        if (!$medicoId) {
            return response()->json([
                'ok' => false,
                'mensagem' => 'Paciente não possui médico associado em atendimentos anteriores.',
            ]);
        }

        $medico = User::find($medicoId);
        if (!$medico) {
            return response()->json([
                'ok' => false,
                'mensagem' => 'Médico não encontrado.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'medico' => [
                'id'   => $medico->id,
                'nome' => $medico->name,
            ],
        ]);
    }

    /**
     * (Opcional) Próxima data disponível na agenda do médico do paciente.
     */
    public function proximaData(Paciente $paciente)
    {
        $resp = $this->medicoDoPaciente($paciente);
        $payload = $resp instanceof JsonResponse ? $resp->getData(true) : $resp;

        if (empty($payload['ok'])) {
            return response()->json([
                'ok' => false,
                'mensagem' => $payload['mensagem'] ?? 'Sem médico associado.',
            ]);
        }

        $medico = $payload['medico'];
        $proxima = $this->proximaDataNaAgenda((int) $medico['id']);

        if (!$proxima) {
            return response()->json([
                'ok' => false,
                'medico' => $medico,
                'mensagem' => 'Só é permitido agendar retorno quando tiver próxima data de consulta.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'medico' => $medico,
            'proxima_data' => $proxima->format('Y-m-d'),
        ]);
    }

    /* ======================= Helpers ======================= */

    /**
     * Busca as datas disponíveis na agenda do médico e normaliza para ['Y-m-d', ...]
     * Aceita retorno em vários formatos (array, JsonResponse, chaves diferentes, aninhado, dd/mm/aaaa).
     */
    protected function datasAgendaDoMedico(int $medicoId): array
    {
        try {
            $raw = app(CadastroDadosController::class)->diasDisponiveisConsulta($medicoId);

            // Normaliza o payload para array PHP
            if ($raw instanceof JsonResponse) {
                $data = $raw->getData(true);
            } elseif (is_array($raw)) {
                $data = $raw;
            } else {
                $data = json_decode((string) $raw, true) ?? [];
            }

            return $this->normalizeAnyDates($data);
        } catch (\Throwable $e) {
            Log::warning('datasAgendaDoMedico falhou: '.$e->getMessage());
            return [];
        }
    }

    /**
     * Pega a primeira data >= hoje.
     */
    protected function proximaDataNaAgenda(int $medicoId): ?Carbon
    {
        $datas = $this->datasAgendaDoMedico($medicoId);
        if (empty($datas)) return null;

        $hoje = Carbon::today();
        foreach ($datas as $ymd) {
            try {
                $c = Carbon::createFromFormat('Y-m-d', $ymd);
                if ($c->isToday() || $c->greaterThan($hoje)) {
                    return $c;
                }
            } catch (\Throwable $e) {
                // ignora formatos inválidos já que normalizamos antes
            }
        }
        return null;
    }

    /**
     * Verifica se uma data específica existe na agenda do médico.
     */
    protected function dataExisteNaAgenda(int $medicoId, string $dataYmd): bool
    {
        $datas = $this->datasAgendaDoMedico($medicoId);
        return in_array($dataYmd, $datas, true);
    }

    /**
     * Converte qualquer estrutura (arrays aninhados/objetos) em lista de Y-m-d.
     */
    protected function normalizeAnyDates($payload): array
    {
        $result = [];
        $stack = [$payload];

        while (!empty($stack)) {
            $cur = array_pop($stack);

            if (is_string($cur)) {
                $ymd = $this->toYmd($cur);
                if ($ymd) $result[] = $ymd;
            } elseif (is_array($cur)) {
                foreach ($cur as $v) {
                    $stack[] = $v;
                }
            } elseif (is_object($cur)) {
                foreach (get_object_vars($cur) as $v) {
                    $stack[] = $v;
                }
            }
        }

        $result = array_values(array_unique(array_filter($result)));
        sort($result);
        return $result;
    }

    /**
     * Normaliza string de data para Y-m-d. Aceita Y-m-d, d/m/Y e strings parseáveis.
     */
    protected function toYmd(string $s): ?string
    {
        $s = trim($s);
        if ($s === '') return null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $s, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
