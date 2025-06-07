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
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('procedimento')->after('observacoes');
            $table->decimal('preco', 8, 2)->nullable()->after('procedimento');
            $table->boolean('pago')->default(false)->after('preco');
            $table->string('forma_pagamento')->nullable()->after('pago');
            $table->date('data_pagamento')->nullable()->after('forma_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn([
                'procedimento',
                'preco',
                'pago',
                'forma_pagamento',
                'data_pagamento',
            ]);
        });
    }
};

