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
        Schema::create('chamadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('senha_id'); // FK da senha
            $table->unsignedBigInteger('medico_id'); // FK do médico que chamou
            $table->integer('tentativa')->default(1); // Número da tentativa (1, 2 ou 3)
            $table->timestamps();

            $table->foreign('senha_id')->references('id')->on('senhas_atendimento')->onDelete('cascade');
            $table->foreign('medico_id')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chamadas');
    }
};
