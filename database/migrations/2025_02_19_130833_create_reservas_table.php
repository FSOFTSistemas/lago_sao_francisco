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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quarto_id');
            $table->unsignedBigInteger('reserva_id');
            $table->date('data_checkin');
            $table->date('data_checkout');
            $table->decimal('valor_diaria', 10, 2);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->enum('situacao', ['pre-reserva', 'reserva', 'hospedado', 'bloqueado']);
            $table->string('n_adultos')->default(1);
            $table->string('n_criancas')->default(0);
            $table->string('observacoes')->nullable();
            $table->foreignId('quarto_id')->constrained()->onDelete('cascade');
            $table->foreignId('hospede_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
