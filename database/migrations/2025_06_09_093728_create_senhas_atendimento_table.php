<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('senhas_atendimento', function (Blueprint $table) {
        $table->id();
        $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
        $table->enum('tipo', ['convencional', 'prioridade']);
        $table->string('codigo'); // Exemplo: C001, PR002
        $table->date('data_emissao');
        $table->timestamps();

        $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senhas_atendimento');
    }
};
