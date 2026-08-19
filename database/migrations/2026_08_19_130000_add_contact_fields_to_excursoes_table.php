<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->string('responsavel')->default('Não informado')->after('status');
            $table->string('telefone_responsavel', 20)->default('Não informado')->after('responsavel');
            $table->string('descricao', 200)->default('Não informado')->after('telefone_responsavel');
        });
    }

    public function down(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->dropColumn(['responsavel', 'telefone_responsavel', 'descricao']);
        });
    }
};
