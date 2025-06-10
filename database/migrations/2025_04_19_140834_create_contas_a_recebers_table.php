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
        Schema::create('contas_a_receber', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_recebido', 15, 2)->default(0)->nullable();
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->enum('status', ['pendente', 'finalizado'])->default('pendente');
            $table->integer('parcela')->default(1);
            $table->unsignedBigInteger('venda_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_contas_id')->nullable();
            $table->timestamps();

            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('plano_de_contas_id')->references('id')->on('plano_de_contas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_a_receber');
    }
};
