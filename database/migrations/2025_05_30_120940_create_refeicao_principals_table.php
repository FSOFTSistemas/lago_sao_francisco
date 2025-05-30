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
        Schema::create('refeicao_principals', function (Blueprint $table) {
            $table->id();
            $table->string('NomeOpcaoRefeicao');
            $table->decimal('PrecoPorPessoa', 10, 2)->default(0);
            $table->text('DescricaoOpcaoRefeicao')->nullable();
            $table->unsignedBigInteger('CardapioID');
            $table->foreign('CardapioID')->references('id')->on('cardapios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refeicao_principals');
    }
};
