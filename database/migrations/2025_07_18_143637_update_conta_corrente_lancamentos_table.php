<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conta_corrente_lancamentos', function (Blueprint $table) {
            // Removendo a foreign key e a coluna banco_id
            $table->dropForeign(['banco_id']);
            $table->dropColumn('banco_id');

            // Adicionando a nova coluna conta_corrente_id
            $table->unsignedBigInteger('conta_corrente_id');
            $table->foreign('conta_corrente_id')
                  ->references('id')
                  ->on('contas_correntes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('conta_corrente_lancamentos', function (Blueprint $table) {
            // Revertendo a coluna conta_corrente_id
            $table->dropForeign(['conta_corrente_id']);
            $table->dropColumn('conta_corrente_id');

            // Recriando banco_id
            $table->unsignedBigInteger('banco_id');
            $table->foreign('banco_id')
                  ->references('id')
                  ->on('bancos')
                  ->onDelete('cascade');
        });
    }
};
