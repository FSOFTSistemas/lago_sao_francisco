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
        Schema::create('fluxo_caixas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->date('data');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->unsignedBigInteger('movimento_id');
            $table->unsignedBigInteger('caixa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_conta_id');
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('caixa_id')->references('id')->on('caixas')->onDelete('cascade');
            // $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('movimento_id')->references('id')->on('movimentos')->onDelete('cascade');
            $table->foreign('plano_de_conta_id')->references('id')->on('plano_de_contas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluxo_caixas');
    }
};
