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
        Schema::table('nota_fiscals', function (Blueprint $table) {
            $table->string('status', 30)->default('pendente')->after('chave');
            $table->string('cstat', 10)->nullable()->after('status');
            $table->string('protocolo', 60)->nullable()->after('cstat');
            $table->string('motivo_status', 255)->nullable()->after('protocolo');
            $table->timestamp('data_autorizacao')->nullable()->after('motivo_status');
            if (! Schema::hasColumn('nota_fiscals', 'total_notas')) {
                $table->double('total_notas')->default(0)->nullable()->after('total_nota');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_fiscals', function (Blueprint $table) {
            $colunas = ['status', 'cstat', 'protocolo', 'motivo_status', 'data_autorizacao'];
            if (Schema::hasColumn('nota_fiscals', 'total_notas')) {
                $colunas[] = 'total_notas';
            }
            $table->dropColumn($colunas);
        });
    }
};
