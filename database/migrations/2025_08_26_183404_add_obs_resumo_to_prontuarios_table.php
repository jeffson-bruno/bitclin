<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('prontuarios', function (Blueprint $table) {
            $table->text('outras_observacoes')->nullable()->after('revisao_sistemas');
            $table->text('resumo')->nullable()->after('outras_observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('prontuarios', function (Blueprint $table) {
            $table->dropColumn(['outras_observacoes', 'resumo']);
        });
    }
};
