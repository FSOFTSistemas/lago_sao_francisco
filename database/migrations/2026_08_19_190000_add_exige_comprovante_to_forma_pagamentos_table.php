<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->boolean('exige_comprovante')->default(false)->after('descricao');
        });

        DB::table('forma_pagamentos')
            ->whereRaw('LOWER(descricao) LIKE ?', ['%pix%'])
            ->update(['exige_comprovante' => true]);
    }

    public function down(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->dropColumn('exige_comprovante');
        });
    }
};
