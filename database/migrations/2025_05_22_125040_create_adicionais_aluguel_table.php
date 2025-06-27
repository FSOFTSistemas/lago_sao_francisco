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
      
    Schema::create('adicionais_aluguel', function (Blueprint $table) {
        $table->id();
        $table->foreignId('aluguel_id')->constrained()->onDelete('cascade');
        $table->foreignId('adicional_id')->constrained()->onDelete('cascade');
        $table->integer('quantidade')->default(1);
        $table->decimal('valor_total', 10, 2)->default(0);
        $table->text('observacao')->nullable();
        $table->timestamps();
    });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adicionais_aluguel');
    }
};
