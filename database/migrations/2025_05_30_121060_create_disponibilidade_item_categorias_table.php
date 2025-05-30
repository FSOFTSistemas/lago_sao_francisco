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
        Schema::create('disponibilidade_item_categorias', function (Blueprint $table) {
            $table->id('DisponibilidadeID');
            $table->boolean('ItemInclusoPadrao')->default(false);
            $table->integer('OrdemExibicao')->default(0);
            $table->unsignedBigInteger('CategoriaItemID');
            $table->unsignedBigInteger('ItemID');
            $table->foreign('CategoriaItemID')->references('id')->on('categorias_de_itens_cardapios')->onDelete('cascade');
            $table->foreign('ItemID')->references('id')->on('itens_do_cardapios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidade_item_categorias');
    }
};
