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
        Schema::create('aluguels', function (Blueprint $table) {
            $table->id();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('observacoes')->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('desconto', 10, 2)->nullable();
            $table->decimal('acrescimo', 10, 2)->nullable();
            $table->integer('parcelas')->nullable();
            $table->date('vencimento')->nullable();
            $table->string('contrato')->nullable();
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pendente');

            $table->unsignedBigInteger('espaco_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('forma_pagamento_id')->nullable();
            $table->unsignedInteger('numero_pessoas_buffet')->nullable();
            $table->unsignedBigInteger('cardapio_id')->nullable();

            $table->timestamps();

            $table->foreign('cardapio_id')->references('id')->on('cardapios')->onDelete('set null');
            $table->foreign('espaco_id')->references('id')->on('espacos')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluguels');
    }
};
