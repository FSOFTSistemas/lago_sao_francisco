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
        Schema::create('itens_entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrada_id')->constrained('entradas')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->foreignId('almoxarifado_item_id')->nullable()->constrained('almoxarifado_itens')->nullOnDelete();
            $table->string('destino', 20)->default('produto'); // 'produto' (venda) ou 'almoxarifado' (consumo)
            $table->integer('numero_item');
            $table->string('codigo_fornecedor')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('descricao');
            $table->string('ncm', 10)->nullable();
            $table->string('cest', 10)->nullable();
            $table->string('cfop', 10)->nullable();
            $table->string('unidade', 10)->nullable();
            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 6);
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_seguro', 15, 2)->default(0);
            $table->decimal('valor_outras_despesas', 15, 2)->default(0);
            $table->string('cst_icms', 10)->nullable();
            $table->string('csosn', 10)->nullable();
            $table->decimal('base_icms', 15, 2)->default(0);
            $table->decimal('aliquota_icms', 8, 4)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('base_icms_st', 15, 2)->default(0);
            $table->decimal('aliquota_icms_st', 8, 4)->default(0);
            $table->decimal('valor_icms_st', 15, 2)->default(0);
            $table->string('cst_pis', 10)->nullable();
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->string('cst_cofins', 10)->nullable();
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->string('cst_ipi', 10)->nullable();
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->string('cClassTrib', 10)->nullable();
            $table->decimal('pIBS', 8, 4)->nullable();
            $table->decimal('pCBS', 8, 4)->nullable();
            $table->string('cst_ibs_cbs', 10)->nullable();
            $table->timestamps();

            $table->index(['entrada_id', 'numero_item']);
            $table->index(['empresa_id', 'produto_id']);
            $table->index(['empresa_id', 'almoxarifado_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_entradas');
    }
};
