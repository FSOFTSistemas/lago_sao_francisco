<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacote_evento_aluguel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aluguel_id');
            $table->unsignedBigInteger('pacote_evento_id');
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->string('observacao')->nullable();
            $table->timestamps();

            $table->foreign('aluguel_id')->references('id')->on('aluguels')->onDelete('cascade');
            $table->foreign('pacote_evento_id')->references('id')->on('pacotes_evento')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacote_evento_aluguel');
    }
};
