<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cardapios_excursao', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao_cardapio');
            $table->decimal('valor_por_pessoa', 10, 2);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['ativo', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cardapios_excursao');
    }
};
