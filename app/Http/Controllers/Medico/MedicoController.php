<?php

namespace App\Http\Controllers\Medico;

use App\Models\User;
use App\Models\Receita;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Chamada;
use App\Models\SenhaAtendimento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Retorno;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class MedicoController extends Controller
{
    public function index()
    {
        $medicoId = Auth::id();
        $hojeObj  = Carbon::today('America/Sao_Paulo');
        $hoje     = $hojeObj->toDateString();

        // ------- AGENDADOS (consultas de hoje NÃO atendidas) -------
        $consultasAgendadas = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->where(function ($q) {
                $q->whereNull('foi_atendido')->orWhere('foi_atendido', 0);
            })
            ->where(function ($q) {
                $q->whereNull('status_atendimento')->orWhere('status_atendimento', 'agendado');
            })
            ->get()
            ->map(function ($p) use ($hoje) {
                return [
                    'paciente_id'     => $p->id,
                    'tipo'            => 'consulta',
                    'nome'            => $p->nome,
                    'data'            => optional($p->data_consulta)->toDateString() ?? $hoje,
                    'hora'            => null,
                    'telefone'        => $p->telefone,
                    'estado_civil'    => $p->estado_civil,
                    'data_nascimento' => optional($p->data_nascimento)->toDateString(),
                    'senha'           => null,
                ];
            });

        // ------- AGENDADOS (retornos de hoje com status 'agendado') -------
        $retornosAgendados = Retorno::with('paciente:id,nome,estado_civil,data_nascimento')
            ->where('medico_id', $medicoId)
            ->whereDate('data_retorno', $hoje)
            ->where('status', 'agendado')
            ->get()
            ->map(function ($r) {
                return [
                    'paciente_id'     => $r->paciente_id,
                    'tipo'            => 'retorno',
                    'nome'            => $r->paciente?->nome,
                    'data'            => optional($r->data_retorno)->toDateString(),
                    'hora'            => optional($r->data_retorno)->format('H:i') !== '00:00' ? optional($r->data_retorno)->format('H:i') : null,
                    'telefone'        => null,
                    'estado_civil'    => $r->paciente->estado_civil ?? null,
                    'data_nascimento' => optional($r->paciente?->data_nascimento)->toDateString(),
                    'senha'           => null,
                ];
            });

        $agendados = $consultasAgendadas->concat($retornosAgendados)->values();

        // ======= ATENDIDOS (consultas) =======
        $consultasAtendidas = Paciente::where('procedimento', 'consulta')
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->where(function ($q) {
                $q->where('foi_atendido', 1)->orWhere('status_atendimento', 'atendido');
            })
            ->get()
            ->map(fn ($p) => [
                'id'   => $p->id, // <- id do PACIENTE (o front usa isso)
                'nome' => $p->nome,
                'tipo' => 'consulta',
                'data' => optional($p->data_consulta)->toDateString(),
            ]);

        // ======= ATENDIDOS (retornos) =======
        $retornosAtendidos = Retorno::with('paciente:id,nome')
            ->where('medico_id', $medicoId)
            ->whereDate('data_retorno', $hoje)
            ->where('status', 'atendido')
            ->get()
            ->map(fn ($r) => [
                'id'   => $r->paciente_id, // <- id do PACIENTE (pra abrir histórico)
                'nome' => $r->paciente?->nome,
                'tipo' => 'retorno',
                'data' => optional($r->data_retorno)->toDateString(),
            ]);

        $atendidos = $consultasAtendidas->concat($retornosAtendidos)->values();

        return Inertia::render('Medico/Dashboard', [
            'pacientesAgendados' => $agendados,  // fallback do card "Agendados"
            'pacientesAtendidos' => $atendidos,  // fallback do card "Atendidos"
        ]);
    }
    public function chamarSenha($senhaId)
    {
        $medicoId = Auth::id();

        // Conta quantas vezes o médico já chamou essa senha
        $tentativas = Chamada::where('senha_id', $senhaId)
                            ->where('medico_id', $medicoId)
                            ->count();

        if ($tentativas >= 3) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Limite de chamadas atingido para essa senha.'
            ], 403);
        }

        // Registra nova chamada
        Chamada::create([
            'senha_id' => $senhaId,
            'medico_id' => $medicoId,
            'tentativa' => $tentativas + 1,
        ]);

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Senha chamada com sucesso!',
            'tentativas_restantes' => 2 - $tentativas,
        ]);
    }

    

    ////////////////////////////////////////////////////////////////////////////////////////
    public function gerarReceita(Request $request)
    {
        $user = auth()->user();
        if (! $user->hasAnyRole(['doctor','enfermeiro'])) {
            abort(403, 'Sem permissão para gerar receita.');
        }

        $request->validate([
            'paciente_id'           => ['required','exists:pacientes,id'],
            'medicamentos'          => ['required','array','min:1'],
            'medicamentos.*.nome'   => ['required','string'],
        ]);

        $medicoOuEnfermeiro = $user;
        $paciente = \App\Models\Paciente::findOrFail($request->paciente_id);

        // filtra itens sem nome (por segurança, além da validação)
        $itens = collect($request->medicamentos)
            ->filter(fn($m) => !empty(trim($m['nome'] ?? '')))
            ->values()
            ->all();

        $registro = $this->montaRegistro($medicoOuEnfermeiro); // ex: "CRM-PI 12345"

        \App\Models\Receita::create([
            'paciente_id'  => $paciente->id,
            'medico_id'    => $medicoOuEnfermeiro->id,
            'crm'          => $registro,   // salva o texto do registro
            'conteudo'     => $itens,      // Model faz cast pra JSON
            'data_receita' => now(),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.receita', [
            'paciente'     => $paciente,
            'medico'       => $medicoOuEnfermeiro,
            'medicamentos' => $itens,
            'registro'     => $registro,   // <<< PASSANDO 'registro'
            'data'         => now()->format('d/m/Y'),
        ])->setPaper('A4','portrait');

        return $pdf->stream('receita.pdf');
    }


    /** cole isso dentro do mesmo controller, igual ao do Atestado */
    private function montaRegistro($user): ?string
    {
        
        $tipo   = $user->registro_tipo;   // ex.: 'CRM', 'COREN', 'CRP'
        $uf     = $user->registro_uf ? strtoupper($user->registro_uf) : null;
        $numero = $user->registro_numero;

        if ($tipo && $numero) {
            return $tipo . ($uf ? "-{$uf}" : '') . " {$numero}";
        }

        // Fallback por role (se não houver nada cadastrado):
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('doctor'))     return null; // sem CRM cadastrado
            if ($user->hasRole('enfermeiro')) return null; // sem COREN cadastrado
        }

        return null;
    }
    

    public function finalizarAtendimento(Request $request)
{
    $request->validate([
        'paciente_id' => 'required|integer|exists:pacientes,id',
    ]);

    try {
        $medicoId = Auth::id();
        $hoje     = \Carbon\Carbon::today('America/Sao_Paulo')->toDateString();

        // 1) Tenta marcar a CONSULTA do dia como atendida
        $p = \App\Models\Paciente::where('id', $request->paciente_id)
            ->where('medico_id', $medicoId)
            ->whereDate('data_consulta', $hoje)
            ->first();

        if ($p) {
            // Só altera se ainda não estava atendido
            if (!(bool)$p->foi_atendido) {
                $p->foi_atendido = 1;
                // Se você REALMENTE tem a coluna status_atendimento, pode manter a linha abaixo:
                // $p->status_atendimento = 'atendido';
                $p->save();
            }

            return response()->json(['ok' => true, 'tipo' => 'consulta']);
        }

        // 2) Se não achou consulta, tenta marcar o RETORNO do dia como atendido
        $update = \App\Models\Retorno::where('paciente_id', $request->paciente_id)
            ->where('medico_id', $medicoId)
            ->whereDate('data_retorno', $hoje)
            ->update(['status' => 'atendido']);

        // Se update > 0, ok. Se não, nada pra finalizar.
        return response()->json(['ok' => true, 'tipo' => $update ? 'retorno' : 'nenhum']);
    } catch (\Throwable $e) {
        \Log::error('finalizarAtendimento ERRO: '.$e->getMessage(), [
            'paciente_id' => $request->paciente_id,
            'medico_id'   => Auth::id(),
            'trace'       => $e->getTraceAsString(),
        ]);

        return response()->json([
            'ok'   => false,
            'erro' => 'Falha ao finalizar atendimento. Verifique os logs.'
        ], 500);
    }
}


    ///////////////////////////////////////////////////////////////////////////////////////////
    public function agendadosHoje()
    {
        $medico = auth()->user();
        $hoje   = Carbon::today('America/Sao_Paulo')->toDateString();

        // CONSULTAS de hoje, que ainda NÃO foram atendidas
        $consultasHoje = Paciente::with('medico:id,name')
            ->where('procedimento', 'consulta')
            ->where('medico_id', $medico->id)
            ->whereDate('data_consulta', $hoje)
            ->where(function ($q) {
                $q->whereNull('foi_atendido')->orWhere('foi_atendido', 0);
            })
            ->where(function ($q) {
                $q->whereNull('status_atendimento')->orWhere('status_atendimento', 'agendado');
            })
            ->get()
            ->map(function ($p) use ($hoje) {
                $data = $p->data_consulta
                    ? \Illuminate\Support\Carbon::parse($p->data_consulta)->toDateString()
                    : $hoje;

                return [
                    'paciente_id'     => $p->id,
                    'tipo'            => 'consulta',
                    'nome'            => $p->nome,
                    'data'            => $data,
                    'hora'            => null,
                    'telefone'        => $p->telefone,
                    'medico'          => $p->medico?->name,
                    'estado_civil'    => $p->estado_civil,
                    'data_nascimento' => optional($p->data_nascimento)->format('Y-m-d'),
                    'senha'           => null,
                ];
            });

        // RETORNOS de hoje, que ainda NÃO foram atendidos
        $retornosHoje = Retorno::with('paciente:id,nome,estado_civil,data_nascimento')
            ->where('medico_id', $medico->id)
            ->whereDate('data_retorno', $hoje)
            ->where('status', 'agendado') // só os não atendidos
            ->get()
            ->map(function ($r) {
                $data = optional($r->data_retorno)->toDateString();
                $hora = optional($r->data_retorno)->format('H:i');
                return [
                    'paciente_id'     => $r->paciente_id,
                    'tipo'            => 'retorno',
                    'nome'            => $r->paciente?->nome,
                    'data'            => $data,
                    'hora'            => $hora !== '00:00' ? $hora : null,
                    'telefone'        => null,
                    'medico'          => null,
                    'estado_civil'    => $r->paciente->estado_civil ?? null,
                    'data_nascimento' => optional($r->paciente?->data_nascimento)->format('Y-m-d'),
                    'senha'           => null,
                ];
            });

        $agendados = $consultasHoje->concat($retornosHoje)
            ->sortBy([
                fn ($a, $b) => strcmp($a['data'] ?? '', $b['data'] ?? ''),
                fn ($a, $b) => strcmp($a['hora'] ?? '00:00', $b['hora'] ?? '00:00'),
            ])->values();

        return response()->json([
            'itens'               => $agendados,
            'consultasHojeCount'  => $consultasHoje->count(),
            'retornosHojeCount'   => $retornosHoje->count(),
            'totalHoje'           => $consultasHoje->count() + $retornosHoje->count(),
        ]);
    }
    public function atendidosHoje()
    {
        $medico = auth()->user();
        $hoje   = \Carbon\Carbon::today('America/Sao_Paulo');

        // Consultas atendidas hoje
        $consultas = \App\Models\Paciente::where('medico_id', $medico->id)
            ->where('procedimento', 'consulta')
            ->whereDate('data_consulta', $hoje)
            ->where(function ($q) {
                $q->where('foi_atendido', 1)
                ->orWhere('status_atendimento', 'atendido');
            })
            ->get()
            ->map(function ($p) {
                return [
                    'id'   => $p->id, // id do paciente (usado para abrir histórico)
                    'nome' => $p->nome,
                    'tipo' => 'consulta',
                    'data' => optional($p->data_consulta)->toDateString(),
                ];
            });

        // Retornos atendidos hoje
        $retornos = \App\Models\Retorno::with('paciente:id,nome')
            ->where('medico_id', $medico->id)
            ->whereDate('data_retorno', $hoje)
            ->where('status', 'atendido')
            ->get()
            ->map(function ($r) {
                return [
                    'id'   => $r->paciente_id, // também id do paciente
                    'nome' => $r->paciente?->nome,
                    'tipo' => 'retorno',
                    'data' => optional($r->data_retorno)->toDateString(),
                ];
            });

        return response()->json([
            'itens' => $consultas->concat($retornos)->values(),
        ]);
    }

}