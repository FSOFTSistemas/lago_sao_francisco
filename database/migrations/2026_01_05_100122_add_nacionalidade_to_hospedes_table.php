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
        Schema::table('hospedes', function (Blueprint $table) {
            // Adiciona os campos após o CPF para manter organizado
            $table->string('nacionalidade')->nullable()->after('orgao_expedidor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospedes', function (Blueprint $table) {
            $table->dropColumn('nacionalidade');
        });
    }
};