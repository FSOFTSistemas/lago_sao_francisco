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
        Schema::create('motorhomes', function (Blueprint $table) {
            $table->id();
            $table->string('placa')->unique();
            $table->string('modelo')->nullable();
            $table->decimal('comprimento', 4, 2)->nullable();
            $table->string('cor')->nullable();
            $table->foreignId('hospede_id')->nullable()->constrained('hospedes')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motorhomes');
    }
};
