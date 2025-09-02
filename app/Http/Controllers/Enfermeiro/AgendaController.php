<?php

namespace App\Http\Controllers\Enfermeiro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AgendaController extends Controller
{
    public function __construct(){ $this->middleware(['auth','role:enfermeiro']); }

    private function todayLocal(): string
    {
        // garanta sua timezone em config/app.php (America/Fortaleza, por ex.)
        return now(config('app.timezone'))->toDateString();
    }

    public function agendadosHoje()
    {
        $debug = ['ok' => false, 'fonte' => null, 'itens' => 0];
        try {
            $hoje = now(config('app.timezone'))->toDateString();

            // CANDIDATOS de tabelas "clássicas"
            $cands = [
                //  tbl             data               hora             tipo    senha   paciente_id
                ['agendamentos',   'data',            'hora',          'tipo', 'senha','paciente_id'],
                ['consultas',      'data_consulta',   'hora',          'tipo', 'senha','paciente_id'],
                ['agenda',         'data',            'horario',       'tipo', 'senha','paciente_id'],
                ['agendas',        'data',            'horario',       'tipo', 'senha','paciente_id'],
                ['marcacoes',      'data',            'hora',          'tipo', 'senha','paciente_id'],
            ];

            $esq = null;
            foreach ($cands as $c) {
                [$tbl,$colData,$colHora,$colTipo,$colSenha,$colPac] = $c;
                if (!Schema::hasTable($tbl)) continue;
                if (!Schema::hasColumns($tbl, [$colData,$colPac])) continue;
                $esq = compact('tbl','colData','colHora','colTipo','colSenha','colPac');
                $esq['origem'] = 'tabela_agendamentos';
                break;
            }

            // FALLBACK: "agendamento" na PRÓPRIA tabela pacientes
            if (!$esq && Schema::hasTable('pacientes')) {
                $dateCols = ['agendamento_data','data_agendamento','data_consulta','data','retorno_em','retorno_data'];
                $timeCols = ['agendamento_hora','hora','horario'];

                $colData = null; foreach ($dateCols as $d) if (Schema::hasColumn('pacientes',$d)) { $colData = $d; break; }
                $colHora = null; foreach ($timeCols as $h) if (Schema::hasColumn('pacientes',$h)) { $colHora = $h; break; }

                if ($colData) {
                    $esq = [
                        'tbl'      => 'pacientes',
                        'colData'  => $colData,
                        'colHora'  => $colHora,
                        'colTipo'  => null,
                        'colSenha' => null,
                        'colPac'   => 'id',
                        'origem'   => 'pacientes'
                    ];
                }
            }

            if (!$esq) {
                $debug['fonte'] = 'nenhuma-fonte-detectada';
                return response()->json(['itens' => [], 'debug' => $debug]);
            }

            $tbl      = $esq['tbl'];
            $colData  = $esq['colData'];
            $colHora  = $esq['colHora'];
            $colTipo  = $esq['colTipo'];
            $colSenha = $esq['colSenha'];
            $colPac   = $esq['colPac'];

            $q = DB::table($tbl);

            // ====== PACIENTE NOME ======
            // Se a fonte NÃO é "pacientes", tentar join com pacientes
            $pacienteNomeAlias = 'paciente_nome';
            if ($tbl !== 'pacientes') {
                if (Schema::hasTable('pacientes')) {
                    // detectar coluna de nome do paciente
                    $pacNomeCol = null;
                    foreach (['nome','nome_completo','name'] as $candNome) {
                        if (Schema::hasColumn('pacientes',$candNome)) { $pacNomeCol = $candNome; break; }
                    }
                    if ($pacNomeCol && Schema::hasColumns('pacientes',['id'])) {
                        $q->leftJoin('pacientes','pacientes.id',"=","$tbl.$colPac");
                        $q->addSelect(DB::raw("pacientes.$pacNomeCol as $pacienteNomeAlias"));
                    }
                }
            } else {
                // fonte é pacientes: alias direto
                $pacNomeCol = null;
                foreach (['nome','nome_completo','name'] as $candNome) {
                    if (Schema::hasColumn('pacientes',$candNome)) { $pacNomeCol = $candNome; break; }
                }
                if ($pacNomeCol) {
                    $q->addSelect(DB::raw("pacientes.$pacNomeCol as $pacienteNomeAlias"));
                }
            }

            // ====== MÉDICO NOME ======
            // detectar FK do médico na tabela-fonte
            $medFk = null;
            foreach (['medico_id','doctor_id','id_medico','profissional_id','user_id'] as $candFk) {
                if (Schema::hasColumn($tbl, $candFk)) { $medFk = $candFk; break; }
            }

            $medicoNomeAlias = 'medico_nome';
            if ($medFk) {
                // Preferir tabela "medicos" se existir; senão "users"
                if (Schema::hasTable('medicos') && Schema::hasColumns('medicos',['id'])) {
                    $colNomeMed = null;
                    foreach (['nome','name'] as $cand) {
                        if (Schema::hasColumn('medicos',$cand)) { $colNomeMed = $cand; break; }
                    }
                    if ($colNomeMed) {
                        $q->leftJoin('medicos','medicos.id',"=","$tbl.$medFk");
                        $q->addSelect(DB::raw("medicos.$colNomeMed as $medicoNomeAlias"));
                    }
                } elseif (Schema::hasTable('users') && Schema::hasColumns('users',['id'])) {
                    $colNomeUsr = null;
                    foreach (['name','usuario'] as $cand) {
                        if (Schema::hasColumn('users',$cand)) { $colNomeUsr = $cand; break; }
                    }
                    if ($colNomeUsr) {
                        $q->leftJoin('users','users.id',"=","$tbl.$medFk");
                        $q->addSelect(DB::raw("users.$colNomeUsr as $medicoNomeAlias"));
                    }
                }
            }

            // ====== SELECT PRINCIPAIS ======
            $selects = [
                "$tbl.$colPac as paciente_id",
                "$tbl.$colData as data",
            ];
            if ($colHora && Schema::hasColumn($tbl,$colHora))  $selects[] = "$tbl.$colHora as hora";
            if ($colTipo && Schema::hasColumn($tbl,$colTipo))  $selects[] = "$tbl.$colTipo as tipo";
            if ($colSenha && Schema::hasColumn($tbl,$colSenha))$selects[] = "$tbl.$colSenha as senha";

            $q->addSelect($selects);

            // ====== FILTRO: HOJE ======
            $q->whereDate("$tbl.$colData", $hoje);

            // ====== ORDENAÇÃO ======
            if ($colHora && Schema::hasColumn($tbl,$colHora)) {
                $q->orderBy("$tbl.$colHora");
            } else {
                $q->orderBy("$tbl.$colData");
            }

            $itens = $q->get();

            $debug['ok'] = true;
            $debug['fonte'] = [
                'tabela'      => $tbl,
                'colData'     => $colData,
                'colHora'     => $colHora,
                'colTipo'     => $colTipo,
                'colSenha'    => $colSenha,
                'colPaciente' => $colPac,
                'medFk'       => $medFk,
                'origem'      => $esq['origem'],
            ];
            $debug['itens'] = $itens->count();

            return response()->json(['itens' => $itens, 'debug' => $debug]);
        } catch (\Throwable $e) {
            Log::error('Enfermeiro.agendadosHoje: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['itens' => [], 'debug' => ['ok'=>false,'erro'=>$e->getMessage()]]);
        }
    }

    public function atendidosHoje()
    {
        // mantém igual ao anterior; se também estiver em pacientes, podemos adaptar depois
        try {
            $hoje = $this->todayLocal();
            $debug = ['ok'=>false];

            if (Schema::hasTable('atendimentos') && Schema::hasColumns('atendimentos',['data','finalizado','paciente_id'])) {
                $q = DB::table('atendimentos');
                if (Schema::hasTable('pacientes') && Schema::hasColumns('pacientes',['id','nome'])) {
                    $q->leftJoin('pacientes','pacientes.id','=','atendimentos.paciente_id')
                      ->addSelect('pacientes.id','pacientes.nome')
                      ->orderBy('pacientes.nome');
                } else {
                    $q->addSelect('atendimentos.paciente_id as id')
                      ->orderBy('atendimentos.paciente_id');
                }
                $itens = $q->whereDate('atendimentos.data',$hoje)->where('finalizado',1)->get();
                $debug['ok']=true; $debug['fonte']='atendimentos';
                return response()->json(['itens'=>$itens,'debug'=>$debug]);
            }

            // fallback vazio
            $debug['fonte']='nenhuma';
            return response()->json(['itens'=>[],'debug'=>$debug]);

        } catch (\Throwable $e) {
            Log::error('Enfermeiro.atendidosHoje: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['itens' => [], 'debug' => ['ok'=>false,'erro'=>$e->getMessage()]]);
        }
    }

    public function chamarSenha($pacienteId)
    {
        return response()->json(['success' => true, 'mensagem' => 'Senha chamada com sucesso.']);
    }
}
