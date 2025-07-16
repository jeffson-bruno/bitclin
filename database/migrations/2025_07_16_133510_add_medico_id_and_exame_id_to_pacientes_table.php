<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('pacientes', function (Blueprint $table) {
        $table->unsignedBigInteger('medico_id')->nullable()->after('procedimento');
        $table->unsignedBigInteger('exame_id')->nullable()->after('medico_id');

        // Se quiser garantir integridade referencial (opcional):
        $table->foreign('medico_id')->references('id')->on('users')->onDelete('set null');
        $table->foreign('exame_id')->references('id')->on('exames')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('pacientes', function (Blueprint $table) {
        $table->dropForeign(['medico_id']);
        $table->dropForeign(['exame_id']);
        $table->dropColumn(['medico_id', 'exame_id']);
    });
}

};
