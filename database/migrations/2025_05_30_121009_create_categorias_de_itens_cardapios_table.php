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
        Schema::create('categorias_de_itens_cardapios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sessao_cardapio_id')->nullable();
            $table->unsignedBigInteger('refeicao_principal_id')->nullable();
            $table->string('nome_categoria_item');
            $table->integer('numero_escolhas_permitidas');
            $table->boolean('eh_grupo_escolha_exclusiva');
            $table->integer('ordem_exibicao');
            $table->timestamps();

            $table->foreign('sessao_cardapio_id')->references('id')->on('secoes_cardapios')->onDelete('cascade');
            $table->foreign('refeicao_principal_id')->references('id')->on('refeicao_principals')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_de_itens_cardapios');
    }
};
