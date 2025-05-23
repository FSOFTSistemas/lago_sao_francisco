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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->boolean('status');
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->enum('categoria', ['hospedagem', 'alimentos', 'servicos', 'produtos']);
            $table->date('data_pagamento');
            $table->date('data_vencimento')->nullable();
            $table->enum('tipo', ['pagamento', 'desconto']);
            $table->decimal('valor', 10, 2);
            $table->string('observacoes')->nullable();
            $table->unsignedBigInteger('reserva_id');

            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos')->onDelete('cascade');
            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacaos');
    }
};
