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
        Schema::create('nota_fiscals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('ncm_id');
            $table->unsignedBigInteger('cfop_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->date('data');
            $table->string('chave')->nullable();
            $table->integer('serie');
            $table->integer('numero');
            $table->string('observacoes');
            $table->string('info_complementares');
            $table->double('peso_liquido');
            $table->double('peso_bruto');
            $table->integer('tp_frete');
            $table->integer('tp_transporte');
            $table->integer('tp_nota');
            $table->string('nfe_referenciavel')->nullable();
            $table->double('total_produtos');
            $table->double('total_nota');
            $table->double('total_desconto');
            $table->double('outras_despesas');
            $table->double('base_ICMS');
            $table->double('vICMS');
            $table->double('base_ST');
            $table->double('vST');
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('ncm_id')->references('id')->on('ncm');
            $table->foreign('cfop_id')->references('id')->on('cfop');
            $table->foreign('usuario_id')->references('id')->on('usuario');
            $table->foreign('empresa_id')->references('id')->on('empresa');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_fiscals');
    }
};
