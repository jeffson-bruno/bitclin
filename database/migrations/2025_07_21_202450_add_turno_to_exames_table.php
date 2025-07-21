<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('exames', function (Blueprint $table) {
        $table->string('turno')->default('ambos'); // opções: 'manha', 'tarde', 'ambos'
    });
}

public function down()
{
    Schema::table('exames', function (Blueprint $table) {
        $table->dropColumn('turno');
    });
}

};
