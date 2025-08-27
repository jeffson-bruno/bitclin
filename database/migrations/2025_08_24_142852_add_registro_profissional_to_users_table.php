<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // caso já exista, pode pular os que conflitam
            if (!Schema::hasColumn('users', 'registro_tipo')) {
                $table->string('registro_tipo', 10)->nullable()->after('email');   // CRM, CRP, COREN...
            }
            if (!Schema::hasColumn('users', 'registro_numero')) {
                $table->string('registro_numero', 30)->nullable()->after('registro_tipo');
            }
            if (!Schema::hasColumn('users', 'registro_uf')) {
                $table->string('registro_uf', 2)->nullable()->after('registro_numero');
            }
            // se o users já tem relação com especialidade, ignore; senão:
            // $table->foreignId('especialidade_id')->nullable()->constrained('especialidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registro_tipo','registro_numero','registro_uf']);
            // $table->dropConstrainedForeignId('especialidade_id');
        });
    }
};
