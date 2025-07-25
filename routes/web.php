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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('receptionist')) return redirect()->route('recepcao.dashboard');
    if ($user->hasRole('doctor')) return redirect()->route('medico.dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

//ROTAS DO CADASTRO DE DADOS
Route::middleware(['auth'])->prefix('cadastro')->group(function () {
    Route::get('/agenda-medica/medico/{id}/dias', [CadastroDadosController::class, 'diasDisponiveisConsulta']);
    Route::get('/agenda-medica/medico/{id}/preco', [CadastroDadosController::class, 'precoConsulta']);
    Route::get('/exames/{id}/info', [CadastroDadosController::class, 'infoExame']);
    Route::put('/pacientes/{id}/reagendar', [CadastroDadosController::class, 'reagendar']);
});

Route::middleware(['auth', 'role:receptionist'])->group(function () {
    Route::get('/recepcao', [RecepcaoController::class, 'index'])->name('recepcao.dashboard');
    Route::get('/recepcao/consultas', [RecepcaoController::class, 'consultas'])->name('recepcao.consultas');
    Route::get('/recepcao/consultas-e-agendamentos', [RecepcaoController::class, 'consultasEAgendamentos']);
    Route::get('/recepcao/horarios-medicos', [RecepcaoController::class, 'horariosMedicos']);
    Route::get('/recepcao/agendamentos-semana', [RecepcaoController::class, 'agendamentosDaSemana']);
    Route::get('/recepcao/pacientes/exames-semana', [RecepcaoController::class, 'pacientesExamesSemana']);
    Route::get('/recepcao/pacientes', [RecepcaoController::class, 'pacientes'])->name('recepcao.pacientes');
    Route::get('/recepcao/consultas-hoje', [RecepcaoController::class, 'consultasHoje']);
});

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

    Route::get('/pacientes/consultas-hoje', [AdminController::class, 'pacientesConsultaHoje']);
    Route::get('/pacientes/exames-semana', [AdminController::class, 'pacientesExamesSemana']);
});

Route::middleware(['auth', 'role:doctor'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [MedicoController::class, 'index'])->name('dashboard');
});

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

    Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
    Route::put('/pacientes/reagendar/{id}', [PacienteController::class, 'reagendar'])->name('pacientes.reagendar');
});

require __DIR__.'/auth.php';
