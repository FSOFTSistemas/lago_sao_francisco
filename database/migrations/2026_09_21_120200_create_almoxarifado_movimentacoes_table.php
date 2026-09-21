<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almoxarifado_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained('almoxarifado_itens')
                ->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'saida']);
            $table->decimal('quantidade', 12, 2);
            $table->dateTime('data_movimentacao');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Campos de Entrada (Recebimento)
            $table->string('fornecedor')->nullable();
            $table->string('recebido_por')->nullable();

            // Campos de Saída (Uso)
            $table->string('retirado_por')->nullable();
            $table->string('setor')->nullable();

            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'item_id']);
            $table->index(['empresa_id', 'tipo']);
            $table->index(['empresa_id', 'data_movimentacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almoxarifado_movimentacoes');
    }
};
