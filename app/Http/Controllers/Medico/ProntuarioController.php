<?php

namespace App\Http\Controllers\Medico;

use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Models\Prontuario;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ProntuarioController extends Controller
{
    // Salva um novo prontuário (usado ao finalizar o atendimento)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_id' => 'required|exists:users,id',
            'queixa_principal' => 'nullable|string',
            'historia_doenca' => 'nullable|string',
            'historico_progressivo' => 'nullable|string',
            'historico_familiar' => 'nullable|string',
            'habitos_vida' => 'nullable|string',
            'revisao_sistemas' => 'nullable|string',
            'receitas' => 'nullable|array',
            'atestados' => 'nullable|array',
            'exames' => 'nullable|array',
            'outras_observacoes' => 'nullable|string',
            'resumo'       => 'nullable|string',

        ]);

        $dataAtendimento = now()->toDateString(); // yyyy-mm-dd

        // Procura um prontuário existente para o mesmo paciente e data
        $prontuario = Prontuario::where('paciente_id', $validated['paciente_id'])
            ->where('data_atendimento', $dataAtendimento)
            ->first();

        if ($prontuario) {
            // Atualiza apenas os campos que vieram preenchidos
            foreach ($validated as $campo => $valor) {
                if (in_array($campo, ['receitas', 'atestados', 'exames']) && $valor) {
                    // Adiciona aos existentes (mescla arrays)
                    $existente = $prontuario->$campo ?? [];
                    $prontuario->$campo = array_merge($existente, $valor);
                } elseif (!is_array($valor) && !is_null($valor)) {
                    // Atualiza apenas se veio preenchido
                    $prontuario->$campo = $valor;
                }
            }

            $prontuario->save();
        } else {
            // Cria um novo prontuário com data do atendimento
            $validated['data_atendimento'] = $dataAtendimento;
            $prontuario = Prontuario::create($validated);
        }

        return response()->json(['success' => true, 'message' => 'Prontuário atualizado com sucesso!']);
    }

    // Visualiza o prontuário do paciente
    public function visualizar(Paciente $paciente)
    {
        // Prontuários salvos pelo médico (mantém seu mapeamento atual)
        $prontuarios = \App\Models\Prontuario::where('paciente_id', $paciente->id)
            ->orderBy('data_atendimento', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'data_atendimento' => Carbon::parse($p->data_atendimento)->format('Y-m-d'),
                    'anamnese' => [
                        'queixa_principal'      => $p->queixa_principal,
                        'historia_doenca'       => $p->historia_doenca,
                        'historico_progressivo' => $p->historico_progressivo ?? $p->historico_medico,
                        'historico_familiar'    => $p->historico_familiar,
                        'habitos_vida'          => $p->habitos_vida,
                        'revisao_sistemas'      => $p->revisao_sistemas,
                        'outras_observacoes'    => $p->outras_observacoes ?? $p->observacoes,
                        'resumo'                => $p->resumo,
                    ],
                    'receitas'  => $p->receitas ?? null,   // pode conter itens do médico e da enfermagem
                    'exames'    => $p->exames ?? null,
                    'atestados' => $p->atestados ?? null,
                ];
            });

        // --- TRIAGENS (Enfermagem) -------------------------------------------------
        // Detecta o nome real da coluna (com ou sem acento)
        $histCol = null;
        if (Schema::hasColumn('anamneses', 'historia_doenca')) {
            $histCol = 'a.historia_doenca';
        } elseif (Schema::hasColumn('anamneses', 'historia_doença')) {
            // com acento precisa de crase no raw
            $histCol = 'a.`historia_doença`';
        }

        $triagensRaw = DB::table('anamneses as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->where('a.paciente_id', $paciente->id)
            ->orderByDesc('a.created_at')
            ->select([
                'a.id',
                'a.pressao_arterial',
                'a.queixa_principal',
                // se não houver nenhuma das colunas, devolve NULL
                DB::raw($histCol ? ($histCol.' as historia_doenca') : 'NULL as historia_doenca'),
                'a.historico_medico',
                'a.historico_familiar',
                'a.habitos_vida',
                'a.revisao_sistemas',
                'a.observacoes',
                'a.origem',
                'a.created_at',
                'u.name as profissional_nome',
                'u.registro_tipo',
                'u.registro_numero',
                'u.registro_uf',
            ])
            ->get();

        $triagens = $triagensRaw->map(function ($a) {
            $registro = null;
            if ($a->registro_tipo && $a->registro_numero) {
                $uf = $a->registro_uf ? '-'.strtoupper($a->registro_uf) : '';
                $registro = $a->registro_tipo.$uf.' '.$a->registro_numero; // ex.: COREN-PI 12345
            }
            return [
                'data'              => $a->created_at ? Carbon::parse($a->created_at)->format('Y-m-d') : null,
                'profissional'      => $a->profissional_nome,
                'registro'          => $registro,
                'origem'            => $a->origem ?: 'enfermeiro',
                'pressao_arterial'  => $a->pressao_arterial,
                'anamnese' => array_filter([
                    'queixa_principal'   => $a->queixa_principal,
                    'historia_doenca'    => $a->historia_doenca,   // já normalizado pelo select
                    'historico_medico'   => $a->historico_medico,
                    'historico_familiar' => $a->historico_familiar,
                    'habitos_vida'       => $a->habitos_vida,
                    'revisao_sistemas'   => $a->revisao_sistemas,
                    'observacoes'        => $a->observacoes,
                ]),
            ];
        });

        return Inertia::render('Medico/ProntuarioPaciente', [
            'paciente'    => $paciente,
            'prontuarios' => $prontuarios,
            'triagens'    => $triagens,
        ]);
    }

    public function gerarPdf($id)
    {
        $paciente = \App\Models\Paciente::findOrFail($id);

        // Prontuários do médico agrupados por data (dd/mm/YYYY)
        $prontuariosPorData = \App\Models\Prontuario::where('paciente_id', $id)
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->data_atendimento)->format('d/m/Y');
            });

        // TRIAGENS/ANAMNESES da enfermagem (mesma query que você usa em visualizar)
        // (traga nome/registro do profissional e normalize historia_doenca)
        $triagensRaw = \DB::table('anamneses as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->where('a.paciente_id', $id)
            ->orderByDesc('a.created_at')
            ->select([
                'a.id', 'a.pressao_arterial', 'a.queixa_principal',
                \DB::raw('a.historia_doenca as historia_doenca'),
                'a.historico_medico','a.historico_familiar','a.habitos_vida','a.revisao_sistemas',
                'a.observacoes','a.origem','a.created_at',
                'u.name as profissional_nome','u.registro_tipo','u.registro_numero','u.registro_uf',
            ])
            ->get();

        // Fallback se a coluna tiver acento
        if ($triagensRaw->isEmpty()) {
            $triagensRaw = \DB::table('anamneses as a')
                ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
                ->where('a.paciente_id', $id)
                ->orderByDesc('a.created_at')
                ->select([
                    'a.id','a.pressao_arterial','a.queixa_principal',
                    \DB::raw('a.`historia_doença` as historia_doenca'),
                    'a.historico_medico','a.historico_familiar','a.habitos_vida','a.revisao_sistemas',
                    'a.observacoes','a.origem','a.created_at',
                    'u.name as profissional_nome','u.registro_tipo','u.registro_numero','u.registro_uf',
                ])
                ->get();
        }

        $triagens = $triagensRaw->map(function ($a) {
            $reg = null;
            if ($a->registro_tipo && $a->registro_numero) {
                $uf  = $a->registro_uf ? '-'.strtoupper($a->registro_uf) : '';
                $reg = $a->registro_tipo.$uf.' '.$a->registro_numero; // COREN-PI 12345
            }
            return [
                'data'         => $a->created_at ? \Carbon\Carbon::parse($a->created_at)->format('d/m/Y') : null,
                'prof'         => $a->profissional_nome,
                'reg'          => $reg,
                'origem'       => $a->origem ?: 'enfermeiro',
                'pa'           => $a->pressao_arterial,
                'anamnese'     => array_filter([
                    'queixa_principal'   => $a->queixa_principal,
                    'historia_doenca'    => $a->historia_doenca,
                    'historico_medico'   => $a->historico_medico,
                    'historico_familiar' => $a->historico_familiar,
                    'habitos_vida'       => $a->habitos_vida,
                    'revisao_sistemas'   => $a->revisao_sistemas,
                    'observacoes'        => $a->observacoes,
                ]),
            ];
        });

        $triagensPorData = $triagens->groupBy('data');

        // **Se não há nada para imprimir, retorne 404**
        if ($prontuariosPorData->isEmpty() && $triagensPorData->isEmpty()) {
            return response()->json(['message' => 'Paciente sem prontuário no momento'], 404);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.historico-clinico', [
            'paciente'          => $paciente,
            'prontuariosPorData'=> $prontuariosPorData,
            'triagensPorData'   => $triagensPorData,
        ])->setPaper('a4');

        return $pdf->download('historico_clinico_'.$paciente->nome.'.pdf');
    }


    public function gerarPdfRecepcao($id)
{
    $paciente = \App\Models\Paciente::with('prontuarios')->findOrFail($id);

    $prontuariosPorData = $paciente->prontuarios
        ->sortByDesc('created_at')
        ->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('d/m/Y');
        });

    // (opcional) repetir a lógica do histCol e triagensPorData:
    $histCol = null;
    if (Schema::hasColumn('anamneses', 'historia_doenca')) {
        $histCol = 'a.historia_doenca';
    } elseif (Schema::hasColumn('anamneses', 'historia_doença')) {
        $histCol = 'a.`historia_doença`';
    }

    $triagensRaw = DB::table('anamneses as a')
        ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
        ->where('a.paciente_id', $id)
        ->orderByDesc('a.created_at')
        ->select([
            'a.id',
            'a.pressao_arterial',
            'a.queixa_principal',
            DB::raw($histCol ? ($histCol.' as historia_doenca') : 'NULL as historia_doenca'),
            'a.historico_medico',
            'a.historico_familiar',
            'a.habitos_vida',
            'a.revisao_sistemas',
            'a.observacoes',
            'a.origem',
            'a.created_at',
            'u.name as profissional_nome',
            'u.registro_tipo',
            'u.registro_numero',
            'u.registro_uf',
        ])
        ->get()
        ->map(function ($a) {
            $registro = null;
            if ($a->registro_tipo && $a->registro_numero) {
                $uf = $a->registro_uf ? '-'.strtoupper($a->registro_uf) : '';
                $registro = $a->registro_tipo.$uf.' '.$a->registro_numero;
            }
            return [
                'data'   => $a->created_at ? Carbon::parse($a->created_at)->format('d/m/Y') : null,
                'prof'   => $a->profissional_nome,
                'reg'    => $registro,
                'origem' => $a->origem ?: 'enfermeiro',
                'pa'     => $a->pressao_arterial,
                'anamnese' => array_filter([
                    'queixa_principal'   => $a->queixa_principal,
                    'historia_doenca'    => $a->historia_doenca,
                    'historico_medico'   => $a->historico_medico,
                    'historico_familiar' => $a->historico_familiar,
                    'habitos_vida'       => $a->habitos_vida,
                    'revisao_sistemas'   => $a->revisao_sistemas,
                    'observacoes'        => $a->observacoes,
                ]),
            ];
        });

    $triagensPorData = $triagensRaw->groupBy('data');

    $pdf = Pdf::loadView('pdfs.historico-clinico', [
        'paciente'           => $paciente,
        'prontuariosPorData' => $prontuariosPorData,
        'triagensPorData'    => $triagensPorData, // também no PDF da recepção
    ])->setPaper('a4');

    return $pdf->stream("historico-clinico-{$paciente->nome}.pdf");
}



}
