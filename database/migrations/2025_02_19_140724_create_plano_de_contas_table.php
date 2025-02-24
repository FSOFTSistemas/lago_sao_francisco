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
        Schema::create('planos_de_contas', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->string('descricao');
            $table->enum('tipo', ['receita', 'despesa']);
            $table->unsignedBigInteger('plano_de_conta_pai')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('plano_de_conta_pai')->references('id')->on('planos_de_contas')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planos_de_contas');
    }
};
