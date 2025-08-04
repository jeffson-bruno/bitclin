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
        $prontuarios = Prontuario::where('paciente_id', $paciente->id)
            ->orderBy('data_atendimento', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'data_atendimento' => \Carbon\Carbon::parse($p->data_atendimento)->format('Y-m-d'),
                    'anamnese' => [
                        'queixa_principal' => $p->queixa_principal,
                        'historia_doenca' => $p->historia_doenca,
                        'historico_progressivo' => $p->historico_progressivo,
                        'historico_familiar' => $p->historico_familiar,
                        'habitos_vida' => $p->habitos_vida,
                        'revisao_sistemas' => $p->revisao_sistemas,
                    ],
                    'receita' => $p->receitas ?? null,      // Pode conter múltiplos medicamentos
                    'exames' => $p->exames ?? null,          // Array de exames solicitados
                    'atestados' => $p->atestados ?? null,    // Array de atestados completos (não apenas o primeiro)
                ];
            });

        return Inertia::render('Medico/ProntuarioPaciente', [
            'paciente' => $paciente,
            'prontuarios' => $prontuarios,
        ]);
    }

    public function gerarPdf($id)
    {
        $paciente = Paciente::findOrFail($id);
        
        // Busca os prontuários agrupados por data
        $prontuariosPorData = Prontuario::where('paciente_id', $id)
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->data_atendimento)->format('d/m/Y');
            });


        $pdf = Pdf::loadView('pdfs.historico-clinico', [
            'paciente' => $paciente,
            'prontuariosPorData' => $prontuariosPorData
        ]);


        return $pdf->download('historico_clinico_' . $paciente->nome . '.pdf');
    }

    public function gerarPdfRecepcao($id)
    {
        $paciente = Paciente::with('prontuarios')->findOrFail($id);

        // Agrupar prontuários por data
        $prontuariosPorData = $paciente->prontuarios
            ->sortByDesc('created_at')
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->created_at)->format('d/m/Y');
            });

        $pdf = Pdf::loadView('pdfs.historico-clinico', compact('paciente', 'prontuariosPorData'))
            ->setPaper('a4');

        return $pdf->stream("historico-clinico-{$paciente->nome}.pdf");
    }



}
