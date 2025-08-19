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
        Schema::create('logs_reserva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->enum('tipo', ['criacao', 'edicao', 'exclusao', 'produto_adicionado', 'produto_removido', 'pagamento_adicionado', 'pagamento_removido', 'status_alterado']);
            $table->text('descricao');
            $table->json('dados_antigos')->nullable();
            $table->json('dados_novos')->nullable();
            $table->timestamps();
            
            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_reserva');
    }
};