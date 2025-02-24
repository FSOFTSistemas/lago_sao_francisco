<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor_inicial', 15, 2)->default(0);
            $table->decimal('valor_final', 15, 2)->nullable();
            $table->dateTime('data_abertura');
            $table->dateTime('data_fechamento')->nullable();
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            $table->unsignedBigInteger('usuario_abertura_id');
            $table->unsignedBigInteger('usuario_fechamento_id')->nullable();
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('usuario_abertura_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('usuario_fechamento_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
