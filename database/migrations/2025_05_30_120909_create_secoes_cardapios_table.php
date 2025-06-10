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
        Schema::create('secoes_cardapios', function (Blueprint $table) {
            $table->id();
            $table->string('nome_secao_cardapio');
            $table->boolean('opcao_conteudo_principal_refeicao');
            $table->integer('ordem_exibicao');
            $table->unsignedBigInteger('cardapio_id');
            $table->foreign('cardapio_id')->references('id')->on('cardapios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secoes_cardapios');
    }
};
