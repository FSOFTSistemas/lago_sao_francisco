<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recebimento_excursao', function (Blueprint $table) {
            $table->foreignId('fluxo_caixa_id')
                ->nullable()
                ->after('forma_pagamento_id')
                ->constrained('fluxo_caixas')
                ->nullOnDelete();
            $table->foreignId('fluxo_cancelamento_id')
                ->nullable()
                ->after('fluxo_caixa_id')
                ->constrained('fluxo_caixas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recebimento_excursao', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fluxo_cancelamento_id');
            $table->dropConstrainedForeignId('fluxo_caixa_id');
        });
    }
};
