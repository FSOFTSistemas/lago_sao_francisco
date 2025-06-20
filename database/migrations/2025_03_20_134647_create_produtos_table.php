<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->unsignedBigInteger('categoria_produto_id');
            $table->boolean('ativo');
            $table->string('ean')->nullable();
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('preco_venda', 10, 2);
            $table->string('ncm')->nullable();
            $table->string('cst')->nullable();
            $table->string('cfop_interno')->nullable();
            $table->string('cfop_externo')->nullable();
            $table->decimal('aliquota', 5, 2)->nullable();
            $table->string('csosn')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->string('comissao')->nullable();
            $table->string('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('categoria_produto_id')->references('id')->on('categoria_produtos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
