<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->integer('grupo_id')->nullable()->after('parcela');
            
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            $table->dropColumn('grupo_id');
        });
    }
};