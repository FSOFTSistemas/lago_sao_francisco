<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Adicionando a coluna forma_pagamento
            $table->string('forma_pagamento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Removendo forma_pagamento
            $table->dropColumn('forma_pagamento');
        });
    }
};
