<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mov_day_uses', function (Blueprint $table) {
            $table->decimal('valor_unitario', 10, 2)->after('quantidade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mov_day_uses', function (Blueprint $table) {
            $table->dropColumn('valor_unitario');
        });
    }
};
