<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('prontuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('users')->onDelete('cascade');

            // Campos de anamnese
            $table->text('queixa_principal')->nullable();
            $table->text('historia_doenca')->nullable();
            $table->text('historico_progressivo')->nullable();
            $table->text('historico_familiar')->nullable();
            $table->text('habitos_vida')->nullable();
            $table->text('revisao_sistemas')->nullable();

            // Arquivos relacionados
            $table->json('receitas')->nullable(); // nomes de arquivos
            $table->json('atestados')->nullable();
            $table->json('exames')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('prontuarios');
    }
};
