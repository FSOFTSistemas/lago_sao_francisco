<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conta_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas_a_receber')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->date('data_recebimento');
            $table->decimal('valor_recebido', 10, 2);
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_pagamentos');
    }
};

