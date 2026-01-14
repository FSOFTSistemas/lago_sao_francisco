<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Criar tabela de Temporadas
        Schema::create('temporadas', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Alta Temporada 2026, Carnaval, etc
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->timestamps();
        });

        // 2. Alterar tabela de Tarifas
        Schema::table('tarifas', function (Blueprint $table) {
            // Adiciona campos de data e flag de alta temporada
            $table->date('data_inicio')->nullable(); // Para saber a validade da tarifa
            $table->date('data_fim')->nullable();
            $table->boolean('alta_temporada')->default(false);
            
            // Se existir chave estrangeira unique ou one-to-one antiga, pode ser necessário dropar índices aqui
            // Ex: $table->dropUnique(['categoria_id']); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('temporadas');
        Schema::table('tarifas', function (Blueprint $table) {
            $table->dropColumn(['data_inicio', 'data_fim', 'alta_temporada']);
        });
    }
};