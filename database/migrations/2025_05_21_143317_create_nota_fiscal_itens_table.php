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
        Schema::create('nota_fiscal_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_fical_id');
            $table->unsignedBigInteger('produto_id');
            $table->integer('quantidade');
            $table->double('v_unitario');
            $table->double('desconto');
            $table->double('subtotal');
            $table->string('cst');
            $table->unsignedBigInteger('cfop_id');
            $table->string('csosm');
            $table->double('total');
            $table->double('base_ICMS');
            $table->double('vICMS');
            $table->double('base_ST');
            $table->double('v_ST');
            $table->timestamps();

            $table->foreign('nota_fical_id')->references('id')->on('nota_fiscals')->onDelete('cascade');;
            $table->foreign('produto_id')->references('id')->on('produtos');
            // $table->foreign('cfop_id')->refereces('id')->on('cfop');//comentado pois a tabela ainda nao existe

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_fiscal_itens');
    }
};
