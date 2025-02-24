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
        Schema::create('vendas_cartoes', function (Blueprint $table) {
            $table->id(); // Chave primária
            $table->unsignedBigInteger('conta_id');
            $table->unsignedBigInteger('banco_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('venda_id');
            $table->decimal('valor', 15, 2);
            $table->date('data_baixa')->nullable();
            $table->enum('status', ['pendente', 'finalizado', 'cancelado'])->default('pendente');
            $table->decimal('taxa', 10, 2)->default(0)->nullable();
            $table->integer('parcela')->default(1)->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('conta_id')->references('id')->on('contas_correntes')->onDelete('cascade');
            $table->foreign('banco_id')->references('id')->on('bancos')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas_cartoes');
    }
};
