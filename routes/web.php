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
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EspecialidadeController;
use App\Http\Controllers\Medico\MedicoController;
use App\Http\Controllers\Admin\AgendaMedicaController;
use App\Http\Controllers\Admin\FinanceiroController;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

//Rotas Essenciais
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('receptionist')) {
        return redirect()->route('recepcao.dashboard');
    }

    if ($user->hasRole('doctor')) {
       return redirect()->route('medico.dashboard');
   }

})->middleware(['auth', 'verified'])->name('dashboard');

//Recepção
Route::middleware(['auth', 'role:receptionist'])->group(function () {
    Route::get('/recepcao', [RecepcaoController::class, 'index'])->name('recepcao.dashboard');
});

//Rotas do Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Especialidades
    Route::resource('especialidades', EspecialidadeController::class)
        ->names('especialidades')
        ->only(['index', 'store', 'update', 'destroy']);
    //Exames
    Route::resource('exames', \App\Http\Controllers\ExameController::class)->except(['show']);
    //Agenda
    Route::resource('agenda-medica', AgendaMedicaController::class)->except(['show']);
    //Financeiro
    Route::get('/financeiro', [App\Http\Controllers\Admin\FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro/baixar/{id}', [FinanceiroController::class, 'baixarPagamento'])->name('financeiro.baixar');

    //Despesas
    Route::resource('despesas', \App\Http\Controllers\Admin\DespesaController::class)->names('despesas');
    Route::post('despesas/{id}/baixar', [\App\Http\Controllers\Admin\DespesaController::class, 'baixar'])->name('despesas.baixar');


});

// Painel do Médico
Route::middleware(['auth', 'role:doctor'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [MedicoController::class, 'index'])->name('dashboard');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    // Cadastro
    Route::resource('pacientes', PacienteController::class);
    Route::resource('usuarios', UsuarioController::class);

    // Agendamentos
    //Route::resource('consultas', ConsultaController::class);
    //Route::resource('exames', ExameController::class);

    Route::post('/senhas', [SenhaAtendimentoController::class, 'store']);

    Route::get('/senhas/imprimir/{id}', function ($id) {
        $senha = SenhaAtendimento::with('paciente')->findOrFail($id);

        return Inertia::render('Senhas/Imprimir', [
            'senha' => $senha,
            'clinica' => [
            'nome' => 'Clínica Santa Esperança', // <- aqui você pode futuramente puxar de settings/banco
            'logo' => asset('images/logo-clinica.png') // <- (opcional)
            ]
        ]);
    });

    Route::get('/pacientes/imprimir-ficha/{id}', function ($id) {
    $paciente = Paciente::findOrFail($id);

    $dadosProcedimento = [
        'procedimento' => 'Consulta Clínica Geral',
        'valor' => 80.00,
        'pago' => false,
    ];

    $pdf = Pdf::loadView('pacientes.ficha-pdf', [
        'paciente' => $paciente,
        'procedimento' => $dadosProcedimento
    ]);

    return $pdf->download('ficha-atendimento.pdf'); // ou ->stream() para abrir direto no navegador
});

Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');


Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');








//Fechamento
});
// Authentication Routes
require __DIR__.'/auth.php';
