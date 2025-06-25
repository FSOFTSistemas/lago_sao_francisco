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
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->boolean('vendedor')->default(false)->after('cargo');
            $table->boolean('caixa')->default(false)->after('vendedor');
            $table->string('senha_supervisor')->nullable()->after('caixa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn('vendedor');
            $table->dropColumn('caixa');
            $table->dropColumn('senha_supervisor');
        });
    }
};
