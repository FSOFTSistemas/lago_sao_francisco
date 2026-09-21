<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almoxarifado_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->foreignId('categoria_id')
                ->constrained('almoxarifado_categorias')
                ->cascadeOnDelete();
            $table->string('nome');
            $table->string('unidade_medida', 20)->default('UN');
            $table->decimal('estoque_atual', 12, 2)->default(0);
            $table->decimal('estoque_minimo', 12, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'ativo']);
            $table->index(['empresa_id', 'categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almoxarifado_itens');
    }
};
