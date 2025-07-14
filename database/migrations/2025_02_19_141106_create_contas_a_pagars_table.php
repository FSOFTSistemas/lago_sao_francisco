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
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->nullable()->default(0);
            $table->string('forma_pagamento')->nullable();
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'pago'])->default('pendente');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_contas_id');
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            
            // Novos campos de parcelamento
            $table->unsignedInteger('numero_parcela')->nullable();
            $table->unsignedInteger('total_parcelas')->nullable();

            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('plano_de_contas_id')->references('id')->on('plano_de_contas')->onDelete('cascade');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')->onDelete('set null');
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
