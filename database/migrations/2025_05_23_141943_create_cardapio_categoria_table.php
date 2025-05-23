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
        Schema::create('cardapio_categoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cardapio_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias_cardapio')->onDelete('cascade');
            $table->unsignedInteger('quantidade_itens')->default(1); // Limite por categoria neste cardápio
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardapio_categoria');
    }
};
