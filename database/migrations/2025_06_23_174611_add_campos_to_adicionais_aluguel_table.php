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
        Schema::table('adicionais_aluguel', function (Blueprint $table) {
            $table->integer('quantidade')->default(1);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->text('observacao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adicionais_aluguel', function (Blueprint $table) {
            $table->dropColumn(['quantidade', 'valor_total', 'observacao']);
        });
    }
};
