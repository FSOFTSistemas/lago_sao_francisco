<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Adiciona somente se não existir
            if (!Schema::hasColumn('contas_a_pagar', 'total_parcelas')) {
                $table->unsignedInteger('total_parcelas')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Remove somente se existir
            if (Schema::hasColumn('contas_a_pagar', 'total_parcelas')) {
                $table->dropColumn('total_parcelas');
            }
        });
    }
};