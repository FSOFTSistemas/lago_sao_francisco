<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->enum('status', ['AGENDADO', 'EM_ANDAMENTO', 'REALIZADO', 'CANCELADO'])
                ->default('AGENDADO')
                ->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
