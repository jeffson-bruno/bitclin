<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\SenhaAtendimentoController;
use App\Models\SenhaAtendimento;
use App\Models\Paciente;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\RecepcaoController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EspecialidadeController;
use App\Http\Controllers\Medico\MedicoController;
use App\Http\Controllers\Admin\AgendaMedicaController;
use App\Http\Controllers\Admin\FinanceiroController;
use App\Http\Controllers\Admin\DespesaController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Models\User;
use App\Models\Exame;
use App\Http\Controllers\Api\CadastroDadosController;
use App\Http\Controllers\Medico\ChamadaSenhaController;
use App\Http\Controllers\Medico\AtestadoController;
use App\Http\Controllers\Medico\ExameController;
use App\Http\Controllers\Medico\AtendimentoController;
use App\Http\Controllers\Medico\AnamneseController;
use App\Http\Controllers\Medico\ProntuarioController;
use App\Http\Controllers\RetornoController;
use App\Http\Controllers\Enfermeiro\DashboardController as EnfermeiroDashboard;
use App\Http\Controllers\Enfermeiro\AgendaController as EnfermeiroAgenda;
use App\Http\Controllers\Enfermeiro\AtendimentoController as EnfermeiroAtendimento;
use App\Http\Controllers\Enfermeiro\AnamneseController as EnfermeiroAnamnese;
use App\Http\Controllers\Enfermeiro\EncaminhamentoController as EnfermeiroEncaminhamento;
use App\Http\Controllers\Psicologo\DashboardController as PsicologoDashboard;
use App\Http\Controllers\Medico\AtendimentoController as MedicoAtendimento;
use App\Http\Controllers\Medico\ProntuarioController as MedicoProntuario;
use App\Http\Controllers\Enfermeiro\ReceitaController as EnfermeiroReceita;
use App\Http\Controllers\ArquivosExamesController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('receptionist')) return redirect()->route('recepcao.dashboard');
    if ($user->hasRole('doctor')) return redirect()->route('medico.dashboard');
    if ($user->hasRole('enfermeiro'))   return redirect()->route('enfermeiro.dashboard');
    if ($user->hasRole('psicologo'))    return redirect()->route('psicologo.dashboard');


})->middleware(['auth', 'verified'])->name('dashboard');

//ROTAS DO CADASTRO DE DADOS
Route::middleware(['auth'])->prefix('cadastro')->group(function () {
    Route::get('/agenda-medica/medico/{id}/dias', [CadastroDadosController::class, 'diasDisponiveisConsulta']);
    Route::get('/agenda-medica/medico/{id}/preco', [CadastroDadosController::class, 'precoConsulta']);
    Route::get('/exames/{id}/info', [CadastroDadosController::class, 'infoExame']);
    Route::put('/pacientes/{id}/reagendar', [CadastroDadosController::class, 'reagendar']);
    Route::get('/cadastro/exames-semana', [CadastroDadosController::class, 'pacientesExamesSemana']);

});

Route::middleware(['auth', 'role:admin|receptionist'])->group(function () {
    // Agendar retorno (usado pelo ModalAgendarRetorno.vue -> route('retornos.store'))
    Route::post('/retornos', [RetornoController::class, 'store'])->name('retornos.store');

    // Listar retornos de um paciente (se quiser exibir histórico/retornos futuros na UI)
    Route::get('/retornos/paciente/{paciente}', [RetornoController::class, 'porPaciente'])->name('retornos.paciente');

     // descobre o médico que atendeu esse paciente (ou o médico associado)
    Route::get('/retornos/medico-do-paciente/{paciente}', [RetornoController::class, 'medicoDoPaciente'])
        ->name('retornos.medicoDoPaciente');

    // (opcional) se quiser encapsular tudo no back: próxima data diretamente
    Route::get('/retornos/proxima-data/{paciente}', [RetornoController::class, 'proximaData'])
        ->name('retornos.proximaData');
    // OPCIONAIS (só adicione se já implementou no controller):
    // Reagendar (update)
    // Route::put('/retornos/{retorno}', [RetornoController::class, 'update'])->name('retornos.update');

    // Alterar status (agendado|realizado|cancelado)
    // Route::post('/retornos/{retorno}/status', [RetornoController::class, 'mudarStatus'])->name('retornos.status');

    // Cancelar retorno
    // Route::delete('/retornos/{retorno}', [RetornoController::class, 'destroy'])->name('retornos.destroy');
});


//Rotas de recepção

Route::middleware(['auth', 'role:receptionist'])->group(function () {
    Route::get('/recepcao', [RecepcaoController::class, 'index'])->name('recepcao.dashboard');
    Route::get('/recepcao/consultas', [RecepcaoController::class, 'consultas'])->name('recepcao.consultas');
    Route::get('/recepcao/consultas-e-agendamentos', [RecepcaoController::class, 'consultasEAgendamentos']);
    Route::get('/recepcao/horarios-medicos', [RecepcaoController::class, 'horariosMedicos']);
    Route::get('/recepcao/agendamentos-semana', [RecepcaoController::class, 'agendamentosDaSemana']);
    Route::get('/recepcao/pacientes/exames-semana', [CadastroDadosController::class, 'pacientesExamesSemana']);
    Route::get('/recepcao/pacientes/consultas-hoje', [CadastroDadosController::class, 'pacientesConsultaHoje']);
    Route::get('/recepcao/pacientes', [RecepcaoController::class, 'pacientes'])->name('recepcao.pacientes');
    Route::get('/recepcao/consultas-hoje', [RecepcaoController::class, 'consultasHoje']); 
    Route::post('/recepcao/presenca/{id}', [RecepcaoController::class, 'marcarPresenca']);
    Route::get('/recepcao/buscar-paciente', [RecepcaoController::class, 'buscarPaciente']);
    Route::get('/recepcao/historico-clinico/{id}', [ProntuarioController::class, 'gerarPdfRecepcao'])->name('recepcao.historico-clinico');
    

});

///////////////////////////////////////////////////////////////////////////////////

//Rotas do administrador
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::resource('especialidades', EspecialidadeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('exames', \App\Http\Controllers\ExameController::class)->except(['show']);
    Route::resource('agenda-medica', AgendaMedicaController::class)->except(['show']);
    Route::get('agenda-medica/medico/{id}/preco', [AgendaMedicaController::class, 'buscarPreco'])->name('agenda-medica.buscarPreco');
    Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro/baixar/{id}', [FinanceiroController::class, 'baixarPagamento'])->name('financeiro.baixar');
    Route::resource('despesas', DespesaController::class)->only(['index','store','destroy']);
    Route::post('despesas/{id}/baixar', [DespesaController::class, 'baixar'])->name('despesas.baixar');

    Route::controller(RelatorioController::class)->prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/dia', 'relatorioDia')->name('dia');
        Route::get('/semana', 'relatorioSemana')->name('semana');
        Route::get('/mes', 'relatorioMes')->name('mes');
        Route::get('/anual', 'relatorioAnual')->name('anual');
    });

    Route::get('relatorios/consultas-hoje', function () {
        $hoje = \Carbon\Carbon::today();
        $pacientes = \App\Models\Paciente::where('procedimento', 'consulta')->whereDate('data_consulta', $hoje)->get();
        $pdf = Pdf::loadView('pdfs.relatorios.consultas_hoje', ['pacientes' => $pacientes, 'hoje' => $hoje->format('d/m/Y')]);
        return $pdf->download("relatorio-consultas-$hoje.pdf");
    })->name('relatorios.consultasHoje');

    Route::get('/pacientes/consultas-hoje', [CadastroDadosController::class, 'pacientesConsultaHoje']);
    Route::get('/pacientes/exames-semana', [CadastroDadosController::class, 'pacientesExamesSemana']);

    // Rota da tela do monitor (Inertia)
    Route::get('/monitor', [\App\Http\Controllers\Admin\MonitorController::class, 'index'])->name('monitor');

// Rota da API de dados do monitor
//Route::get('/monitor/dados-chamadas', [\App\Http\Controllers\Admin\MonitorController::class, 'dadosChamadas']);
});

////////////////////////////////////////////////////////////////////////////////////////

//API para o Monitor de Chamadas
// API pública da tela do monitor (não requer login)
Route::get('/monitor/dados-chamadas', [\App\Http\Controllers\Admin\MonitorController::class, 'dadosChamadas']);


//Rotas do médico
Route::middleware(['auth', 'role:doctor'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [MedicoController::class, 'index'])->name('dashboard');
    
    // Chamar senha (com controle de tentativas)
    Route::post('/chamar-senha/{paciente_id}', [ChamadaSenhaController::class, 'chamar'])->name('chamar.senha');

    Route::post('/gerar-receita', [MedicoController::class, 'gerarReceita'])->name('gerar-receita');
    Route::get('/gerar-atestado/{paciente_id}/{cid}/{texto}', [AtestadoController::class, 'gerarPdf']);
    Route::get('/gerar-solicitacao-exames/{paciente_id}/{exames}', [ExameController::class, 'gerarPdf'])->name('gerar-solicitacao-exames');
    Route::post('/salvar-anamnese', [AnamneseController::class, 'store'])->name('salvar-anamnese');
    Route::get('/atendimento/{paciente}', [AtendimentoController::class, 'atender'])->name('medico.atendimento');

    // Rotas do prontuário
    Route::post('/salvar-prontuario', [\App\Http\Controllers\Medico\ProntuarioController::class, 'store'])->name('salvar-prontuario');
    Route::get('/prontuario/{paciente}', [\App\Http\Controllers\Medico\ProntuarioController::class, 'visualizar'])->name('visualizar-prontuario');
    Route::post('/finalizar-atendimento', [MedicoController::class, 'finalizarAtendimento']);
    
    //Route::get('/prontuario/{id}/pdf', [ProntuarioController::class, 'gerarPdf']);

    Route::get('/agendados-hoje', [\App\Http\Controllers\Medico\MedicoController::class, 'agendadosHoje'])
        ->name('agendados-hoje');
    Route::get('/atendidos-hoje', [MedicoController::class, 'atendidosHoje'])
        ->name('atendidos-hoje');

});

///////////////////////////////////////////////////////////////////////////////////////////


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('pacientes', PacienteController::class);
    Route::resource('usuarios', UsuarioController::class);

    Route::post('/senhas', [SenhaAtendimentoController::class, 'store']);

    Route::get('/senhas/imprimir/{id}', function ($id) {
        $senha = SenhaAtendimento::with('paciente')->findOrFail($id);
        return Inertia::render('Senhas/Imprimir', [
            'senha' => $senha,
            'clinica' => [
                'nome' => 'Clínica Santa Esperança',
                'logo' => asset('images/logo-clinica.png')
            ]
        ]);
    });

    Route::get('/pacientes/imprimir-ficha/{id}', function ($id) {
        $paciente = Paciente::with(['medico.especialidade', 'exame'])->findOrFail($id);
        $descricaoProcedimento = 'Não informado';
        if ($paciente->procedimento === 'consulta' && $paciente->medico_id) {
            $medico = User::with('especialidade')->find($paciente->medico_id);
            $especialidade = $medico?->especialidade?->nome ?? 'Especialidade não informada';
            $descricaoProcedimento = "Consulta com $especialidade";
        }
        if ($paciente->procedimento === 'exame' && $paciente->exame_id) {
            $exame = Exame::find($paciente->exame_id);
            $descricaoProcedimento = $exame ? "Exame / {$exame->nome}" : 'Exame não identificado';
        }
        $statusPagamento = 'Não Pago';
        if ($paciente->pago) {
            $statusPagamento = match ($paciente->forma_pagamento) {
                'pix' => 'Pago via Pix',
                'cartao' => 'Pago com Cartão',
                'dinheiro' => 'Pagamento efetuado à vista',
                default => 'Pago'
            };
        }
        $dadosProcedimento = [
            'procedimento' => $descricaoProcedimento,
            'valor' => $paciente->preco ?? 0,
            'pago' => $statusPagamento
        ];
        $pdf = Pdf::loadView('pacientes.ficha-pdf', [
            'paciente' => $paciente,
            'procedimento' => $dadosProcedimento
        ])->setPaper('a4');
        return $pdf->download('ficha-atendimento.pdf');
    });

    //Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
    Route::put('/pacientes/reagendar/{id}', [PacienteController::class, 'reagendar'])->name('pacientes.reagendar');

    Route::middleware(['auth','role:enfermeiro'])
    ->prefix('enfermeiro')
    ->name('enfermeiro.')
    ->group(function () {
        Route::get('/dashboard', [EnfermeiroDashboard::class, 'index'])->name('dashboard');

        // Listas do dia (JSON p/ dashboard)
        Route::get('/agendados-hoje', [EnfermeiroAgenda::class, 'agendadosHoje']);
        Route::get('/atendidos-hoje', [EnfermeiroAgenda::class, 'atendidosHoje']);

        // Ações rápidas
        Route::post('/chamar-senha/{paciente}', [EnfermeiroAgenda::class, 'chamarSenha']);
        Route::get('/atendimento/{paciente}', [EnfermeiroAtendimento::class, 'index'])->name('atendimento');

        // Triagem/Anamnese do enfermeiro
        Route::post('/anamnese', [EnfermeiroAnamnese::class, 'store'])->name('anamnese.store');

        Route::post('/gerar-receita', [EnfermeiroReceita::class, 'gerarReceita'])
            ->name('receita.gerar'); // POST /enfermeiro/gerar-receita

        // Encaminhamento para especialista (quando veio para o enfermeiro)
        Route::post('/encaminhar', [EnfermeiroEncaminhamento::class, 'store'])->name('encaminhar.store');
    });

    // Rotas compartilhadas: médico **e** enfermeiro
    Route::middleware(['auth','role:doctor|enfermeiro'])
        ->prefix('medico')->name('medico.')
        ->group(function () {
            Route::get('/atendimento/{paciente}', [AtendimentoController::class, 'atender'])->name('atendimento');

            // Visualizar prontuário (tela)
            Route::get('/prontuario/{paciente}', [ProntuarioController::class, 'visualizar'])->name('prontuario.visualizar');

            // **BAIXAR PDF do prontuário/histórico**
            Route::get('/prontuario/{paciente}/pdf', [ProntuarioController::class, 'gerarPdf'])
                ->name('prontuario.pdf');
    });

    // Psicólogo
    Route::middleware(['auth','role:psicologo'])
        ->prefix('psicologo')->name('psicologo.')
        ->group(function () {
            Route::get('/dashboard', [PsicologoDashboard::class, 'index'])->name('dashboard');
    });

    // Upload: admin ou recepção
    Route::middleware(['auth','role:admin|receptionist'])->group(function () {
        Route::post('/arquivos-exames', [ArquivosExamesController::class, 'store'])->name('exames-arquivos.store');
        Route::delete('/arquivos-exames/{arquivo}', [ArquivosExamesController::class, 'destroy'])->name('exames-arquivos.destroy');
    });

    // Listar/visualizar: médico e enfermeiro (também admin/recepção podem ver/baixar)
    Route::middleware(['auth','role:admin|receptionist|doctor|enfermeiro'])->group(function () {
        Route::get('/arquivos-exames/paciente/{paciente}', [ArquivosExamesController::class, 'indexPaciente'])->name('exames-arquivos.index-paciente');
        Route::get('/arquivos-exames/{arquivo}/download', [ArquivosExamesController::class, 'download'])->name('exames-arquivos.download');
        Route::get('/arquivos-exames/{arquivo}/ver', [ArquivosExamesController::class, 'inline'])->name('exames-arquivos.inline');
    });


    
});



require __DIR__.'/auth.php';
