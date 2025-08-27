<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('retornos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('medico_id')->nullable()->constrained('users')->nullOnDelete(); // ajuste se médicos não forem 'users'
            $table->dateTime('data_retorno');
            $table->string('motivo', 160)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('status', 20)->default('agendado'); // agendado|realizado|cancelado
            $table->timestamps();
            $table->index(['paciente_id','data_retorno']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('retornos');
    }
};
