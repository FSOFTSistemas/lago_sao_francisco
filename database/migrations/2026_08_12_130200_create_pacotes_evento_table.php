<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacotes_evento', function (Blueprint $table) {
            $table->id();
            $table->enum('categoria', ['ilha_adicional', 'refeicao_staff']);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('observacao_padrao')->nullable();
            $table->year('ano');
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacotes_evento');
    }
};
