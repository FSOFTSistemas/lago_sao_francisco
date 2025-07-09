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
        Schema::create('buffet_escolhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluguel_id')->constrained('aluguels')->onDelete('cascade');
            $table->enum('tipo', ['categoria_item', 'opcao_refeicao']);
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_de_itens_cardapios')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('itens_do_cardapios')->onDelete('cascade');
            $table->foreignId('opcao_refeicao_id')->nullable()->constrained('refeicao_principals')->onDelete('cascade');
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['aluguel_id', 'tipo']);
            $table->index('categoria_id');
            $table->index('item_id');
            $table->index('opcao_refeicao_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buffet_escolhas');
    }
};

