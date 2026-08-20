<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recebimento_excursao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excursao_id')
                ->constrained('excursoes')
                ->cascadeOnDelete();
            $table->date('data_recebimento');
            $table->decimal('valor', 10, 2);
            $table->foreignId('forma_pagamento_id')
                ->constrained('forma_pagamentos')
                ->restrictOnDelete();
            $table->string('comprovante_path')->nullable();
            $table->timestamps();

            $table->index(['excursao_id', 'data_recebimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recebimento_excursao');
    }
};
