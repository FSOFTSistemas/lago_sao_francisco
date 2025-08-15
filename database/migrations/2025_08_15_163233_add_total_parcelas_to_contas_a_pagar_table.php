<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->unsignedInteger('total_parcelas')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            $table->dropColumn('total_parcelas');
        });
    }
};