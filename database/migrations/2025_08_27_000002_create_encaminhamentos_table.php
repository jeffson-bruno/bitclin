<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('encaminhamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id')->index();
            $table->unsignedBigInteger('de_user_id')->index(); // enfermeiro
            $table->unsignedBigInteger('para_medico_id')->nullable()->index();
            $table->unsignedBigInteger('para_especialidade_id')->nullable()->index();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encaminhamentos');
    }
};
