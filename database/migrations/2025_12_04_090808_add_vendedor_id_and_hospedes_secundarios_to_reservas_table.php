<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->integer('vendedor_id')->nullable()->after('canal_venda');
            $table->json('hospedes_secundarios')->nullable()->after('vendedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['vendedor_id', 'hospedes_secundarios']);
        });
    }
};