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
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedors')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dfe_documento_id')->nullable()->constrained('dfe_documentos')->nullOnDelete();
            $table->string('chave', 44)->unique();
            $table->string('numero_nota', 20);
            $table->string('serie', 10)->nullable();
            $table->string('natureza_operacao')->nullable();
            $table->dateTime('data_emissao');
            $table->dateTime('data_entrada');
            $table->unsignedTinyInteger('tipo_operacao')->default(0); // 0: Entrada
            $table->decimal('valor_produtos', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_seguro', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_outras_despesas', 15, 2)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('valor_icms_st', 15, 2)->default(0);
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->longText('xml')->nullable();
            $table->string('status', 30)->default('confirmada'); // rascunho, confirmada, cancelada
            $table->timestamps();

            $table->index(['empresa_id', 'data_entrada']);
            $table->index(['empresa_id', 'fornecedor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entradas');
    }
};
