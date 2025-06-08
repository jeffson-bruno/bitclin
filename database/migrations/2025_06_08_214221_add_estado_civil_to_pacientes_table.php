<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('estado_civil')->nullable();
        });
    }

    public function down()
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn('estado_civil');
        });
    }

};
