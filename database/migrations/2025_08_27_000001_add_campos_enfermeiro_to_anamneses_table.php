<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            $table->string('origem', 20)->nullable()->index(); // 'medico' | 'enfermeiro'
            $table->string('pressao_arterial', 15)->nullable(); // ex: 120/80 mmHg
            $table->unsignedBigInteger('user_id')->nullable()->index(); // quem registrou
        });
    }

    public function down(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            $table->dropColumn(['origem','pressao_arterial','user_id']);
        });
    }
};