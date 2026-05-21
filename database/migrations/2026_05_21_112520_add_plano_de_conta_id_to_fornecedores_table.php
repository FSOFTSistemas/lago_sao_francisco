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
        Schema::table('fornecedors', function (Blueprint $table) {
            $table->unsignedBigInteger('plano_de_conta_id')->nullable()->after('forma_pagamento');

            $table->foreign('plano_de_conta_id')
                ->references('id')
                ->on('plano_de_contas')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornecedors', function (Blueprint $table) {
            $table->dropForeign(['plano_de_conta_id']);
            $table->dropColumn('plano_de_conta_id');
        });
    }
};
