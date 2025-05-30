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
        Schema::create('cardapios', function (Blueprint $table) {
            $table->id();
            $table->string('NomeCardapio');
            $table->year('AnoCardapio')->nullable();
            $table->decimal('PrecoBasePorPessoa', 10, 2)->default(0);
            $table->integer('ValidadeOrcamentoDias')->nullable();
            $table->integer('PoliticaCriancaGratisLimiteIdade')->nullable();
            $table->decimal('PoliticaCriancaDescontoPercentual', 5, 2)->nullable();
            $table->integer('PoliticaCriancaDescontoIdadeInicio')->nullable();
            $table->integer('PoliticaCriancaDescontoIdadeFim')->nullable();
            $table->integer('PoliticaCriancaPrecoIntegralIdadeInicio')->nullable();
            $table->boolean('PossuiOpcaoEscolhaConteudoPrincipalRefeicao')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardapios');
    }
};
