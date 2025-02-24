<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conta_corrente_lancamentos', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->date('data');
            $table->enum('tipo', ['entrada', 'saída']); // Define se é entrada ou saída
            $table->enum('status', ['pendente', 'finalizado'])->default('pendente');
            $table->unsignedBigInteger('banco_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('banco_id')->references('id')->on('bancos')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_corrente_lancamentos');
    }
};
