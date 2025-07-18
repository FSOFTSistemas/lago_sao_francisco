<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Removendo a coluna status antiga
            $table->dropColumn('status');
        });

        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Adicionando a nova coluna status com os valores atualizados
            $table->enum('status', ['pendente', 'pago'])->default('pendente');
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // Revertendo a enum para os valores originais
            $table->dropColumn('status');
        });

        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'finalizado'])->default('pendente');
        });
    }
};
