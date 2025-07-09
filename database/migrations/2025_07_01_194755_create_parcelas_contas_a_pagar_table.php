<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parcelas_contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contas_a_pagar_id');
            $table->integer('numero_parcela');
            $table->decimal('valor', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->enum('status', ['pendente', 'pago'])->default('pendente');
            $table->timestamps();

            $table->foreign('contas_a_pagar_id')->references('id')->on('contas_a_pagar')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas_contas_a_pagar');
    }
};
