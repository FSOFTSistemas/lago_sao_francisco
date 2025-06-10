<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendaItemsTable extends Migration
{
    public function up()
    {
        Schema::create('venda_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('venda_id');
            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('acrescimo', 10, 2)->nullable();
            $table->decimal('deconto', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->foreign('produto_id')->references('id')->on('produtos');
            $table->foreign('venda_id')->references('id')->on('vendas');
        });
    }

    public function down()
    {
        Schema::dropIfExists('venda_items');
    }
}
