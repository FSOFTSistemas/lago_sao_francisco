<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendasTable extends Migration
{
    public function up()
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->unsignedBigInteger('empresa_id');
            $table->date('data');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->text('observacao')->nullable();
            $table->decimal('total', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('desconto', 10, 2)->nullable();
            $table->decimal('acrescimo', 10, 2)->nullable();
            $table->string('situacao');
            $table->boolean('gerado_nf');
            $table->timestamps();

            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendas');
    }
}
