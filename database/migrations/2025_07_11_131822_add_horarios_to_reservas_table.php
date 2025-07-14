<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->time('hora_checkin')->nullable()->after('data_checkin');
            $table->time('hora_checkout')->nullable()->after('data_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['hora_checkin', 'hora_checkout']);
        });
    }
};
