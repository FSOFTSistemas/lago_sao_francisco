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
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('observacoes')->nullable();
            $table->boolean('ativo');
            $table->string('categoria');
            $table->decimal('seg', 10, 2)->nullable();
            $table->decimal('ter', 10, 2)->nullable();
            $table->decimal('qua', 10, 2)->nullable();
            $table->decimal('qui', 10, 2)->nullable();
            $table->decimal('sex', 10, 2)->nullable();
            $table->decimal('sab', 10, 2)->nullable();
            $table->decimal('dom', 10, 2)->nullable();
            $table->unsignedBigInteger('tarifa_hospede_id')->nullable();
            $table->foreign('tarifa_hospede_id')->references('id')->on('tarifa_hospedes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
