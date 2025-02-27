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
        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->nullable()->default(0);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'finalizado'])->default('pendente');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_contas_id')->nullable();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->timestamps();

            // Definição de chaves estrangeiras
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('plano_de_contas_id')->references('id')->on('plano_de_contas')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_pagar');
    }
};
