<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Adicionar campos na tabela de RESERVAS
        Schema::table('reservas', function (Blueprint $table) {
            // Crianças que não contam no cálculo financeiro, mas precisam ser registradas
            $table->integer('n_criancas_nao_pagantes')->default(0)->after('n_criancas');
        });

        // 2. Adicionar campos na tabela de PREFERENCIAS DO HOTEL (Configuração de Preços)
        // Assumindo que a tabela se chama 'preferencias_hotels' baseada no Model PreferenciasHotel
        Schema::table('preferencias_hotels', function (Blueprint $table) {
            $table->decimal('valor_pet_pequeno', 10, 2)->default(0.00)->after('valor_diaria');
            $table->decimal('valor_pet_medio', 10, 2)->default(0.00)->after('valor_pet_pequeno');
            $table->decimal('valor_pet_grande', 10, 2)->default(0.00)->after('valor_pet_medio');
        });

        // 3. Criar nova tabela para vincular PETS à RESERVA
        Schema::create('reserva_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->onDelete('cascade');
            
            // Tamanho: pequeno, medio, grande
            $table->enum('tamanho', ['pequeno', 'medio', 'grande']);
            
            // Quantidade de pets deste tamanho
            $table->integer('quantidade')->default(1);
            
            // Valor cobrado POR DIA para este tamanho (Snapshot do preço no momento da reserva)
            // Isso é importante caso você aumente o preço nas preferências, as reservas antigas não mudam
            $table->decimal('valor_unitario', 10, 2)->default(0.00); 
            
            $table->timestamps();
        });
    }

    public function down()
    {
        // Reverter tabela de pets
        Schema::dropIfExists('reserva_pets');

        // Reverter campos na tabela de preferências
        Schema::table('preferencias_hotels', function (Blueprint $table) {
            $table->dropColumn(['valor_pet_pequeno', 'valor_pet_medio', 'valor_pet_grande']);
        });

        // Reverter campo na tabela de reservas
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('n_criancas_nao_pagantes');
        });
    }
};