<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('senhas_atendimento', function (Blueprint $table) {
            $table->timestamp('chamada_em')->nullable();
        });
    }

    public function down()
    {
        Schema::table('senhas_atendimento', function (Blueprint $table) {
            $table->dropColumn('chamada_em');
        });
    }

};
