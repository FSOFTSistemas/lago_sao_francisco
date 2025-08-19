<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tarifas', function (Blueprint $table) {
            $table->unsignedInteger('padrao_adultos')->nullable()->after('tarifa_hospede_id');
            $table->unsignedInteger('padrao_criancas')->nullable()->after('padrao_adultos');
            $table->decimal('adicional_adulto', 10, 2)->nullable()->after('padrao_criancas');
            $table->decimal('adicional_crianca', 10, 2)->nullable()->after('adicional_adulto');
        });
    }

    public function down(): void
    {
        Schema::table('tarifas', function (Blueprint $table) {
            $table->dropColumn([
                'padrao_adultos',
                'padrao_criancas',
                'adicional_adulto',
                'adicional_crianca',
            ]);
        });
    }
};