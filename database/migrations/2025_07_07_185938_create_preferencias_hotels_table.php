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
        Schema::create('preferencias_hotels', function (Blueprint $table) {
            $table->id();
            $table->timestamp('checkin')->nullable();
            $table->timestamp('checkout')->nullable();
            $table->boolean('limpeza_quarto');
            $table->enum('valor_diaria', ['diaria', 'totalDiaria', 'tarifario']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencias_hotels');
    }
};
