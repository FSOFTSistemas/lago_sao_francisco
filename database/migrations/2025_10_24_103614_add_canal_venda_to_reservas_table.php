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
        // Definimos os valores do ENUM
        $canais = ['WhatsApp', 'Instagram', 'Telefone', 'Indicação', 'Balcão', 'Facebook', 'Email', 'Outros'];

        Schema::table('reservas', function (Blueprint $table) use ($canais) {
            $table->enum('canal_venda', $canais)
                  ->nullable()
                  ->after('placa_veiculo'); // Adiciona a coluna após 'placa_veiculo'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('canal_venda');
        });
    }
};