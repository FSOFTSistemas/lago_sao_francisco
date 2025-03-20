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
            $table->string('tipo');
            $table->string('situacao');
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
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
