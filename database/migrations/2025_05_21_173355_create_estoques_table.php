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
        Schema::create('estoques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('empresa_id');
            $table->double('estoque_atual');
            $table->double('entradas');
            $table->double('saidas');
            $table->timestamps();

            $table->foreign('produto_id')->references('id')->on('produto')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('produto')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoques');
    }
};
