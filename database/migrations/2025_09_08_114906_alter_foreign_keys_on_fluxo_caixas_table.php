<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fluxo_caixas', function (Blueprint $table) {
            // Remover as FKs antigas
            $table->dropForeign(['caixa_id']);
            $table->dropForeign(['plano_de_conta_id']);

            // Recriar sem onDelete
            $table->foreign('caixa_id')
                ->references('id')
                ->on('caixas'); // sem cascade

            $table->foreign('plano_de_conta_id')
                ->references('id')
                ->on('plano_de_contas'); // sem cascade
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fluxo_caixas', function (Blueprint $table) {
            // Remover as FKs recriadas
            $table->dropForeign(['caixa_id']);
            $table->dropForeign(['plano_de_conta_id']);

            // Recriar com cascade novamente
            $table->foreign('caixa_id')
                ->references('id')
                ->on('caixas')
                ->onDelete('cascade');

            $table->foreign('plano_de_conta_id')
                ->references('id')
                ->on('plano_de_contas')
                ->onDelete('cascade');
        });
    }
};
