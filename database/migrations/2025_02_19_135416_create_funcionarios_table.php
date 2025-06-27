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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf')->unique()->nullable();
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->decimal('salario', 10, 2)->nullable();
            $table->date('data_contratacao');
            $table->enum('status', ['ativo', 'inativo']);
            $table->string('setor');
            $table->string('cargo');
            $table->boolean('vendedor')->default(false);
            $table->boolean('caixa')->default(false);
            $table->string('senha_supervisor')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
