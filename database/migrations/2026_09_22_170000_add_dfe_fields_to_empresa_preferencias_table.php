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
        Schema::table('empresa_preferencias', function (Blueprint $table) {
            $table->text('senha_certificado')->nullable()->after('certificado_digital');
            $table->unsignedTinyInteger('ambiente_dfe')->default(1)->after('senha_certificado'); // 1: Produção, 2: Homologação
            $table->string('ult_nsu', 15)->default('0')->after('ambiente_dfe');
            $table->string('max_nsu', 15)->default('0')->after('ult_nsu');
            $table->timestamp('data_ultima_consulta_dfe')->nullable()->after('max_nsu');
            $table->string('cstat_ultima_consulta_dfe', 10)->nullable()->after('data_ultima_consulta_dfe');
            $table->string('motivo_ultima_consulta_dfe', 255)->nullable()->after('cstat_ultima_consulta_dfe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa_preferencias', function (Blueprint $table) {
            $table->dropColumn([
                'senha_certificado',
                'ambiente_dfe',
                'ult_nsu',
                'max_nsu',
                'data_ultima_consulta_dfe',
                'cstat_ultima_consulta_dfe',
                'motivo_ultima_consulta_dfe',
            ]);
        });
    }
};
