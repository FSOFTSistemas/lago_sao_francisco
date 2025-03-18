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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_razao_social');
            $table->string('apelido_nome_fantasia')->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cpf_cnpj')->unique();
            $table->string('rg_ie')->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->enum('tipo', ['PF', 'PJ']);
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
