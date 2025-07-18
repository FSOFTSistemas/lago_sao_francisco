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
        Schema::create('conta_corrente_lancamentos', function (Blueprint $table) {
            $table->id(); 
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->date('data');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->enum('status', ['pendente', 'finalizado'])->default('pendente');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('conta_corrente_id');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('conta_corrente_id')->references('id')->on('contas_correntes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_corrente_lancamentos');
    }
};
