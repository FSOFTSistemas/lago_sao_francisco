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
        Schema::create('aluguel_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluguel_id')->constrained('aluguels')->onDelete('cascade');
            $table->foreignId('forma_pagamento_id')->constrained('forma_pagamentos')->onDelete('restrict');
            $table->decimal('valor', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['aluguel_id', 'forma_pagamento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluguel_pagamentos');
    }
};

