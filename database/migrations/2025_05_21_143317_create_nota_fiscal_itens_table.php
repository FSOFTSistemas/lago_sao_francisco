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
            $table->integer('nota_fical_id');
            $table->integer('produto_id');
            $table->integer('quantidade');
            $table->double('v_unitario');
            $table->double('desconto');
            $table->double('subtotal');
            $table->string('cst');
            $table->integer('cfop_id');
            $table->string('csosm');
            $table->double('total');
            $table->double('base_ICMS');
            $table->double('vICMS');
            $table->double('base_ST');
            $table->double('v_ST');
            $table->timestamps();
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
