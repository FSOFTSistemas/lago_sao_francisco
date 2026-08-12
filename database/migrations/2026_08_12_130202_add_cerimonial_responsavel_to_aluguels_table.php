<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aluguels', function (Blueprint $table) {
            if (! Schema::hasColumn('aluguels', 'cerimonial_responsavel')) {
                $table->string('cerimonial_responsavel')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aluguels', function (Blueprint $table) {
            if (Schema::hasColumn('aluguels', 'cerimonial_responsavel')) {
                $table->dropColumn('cerimonial_responsavel');
            }
        });
    }
};
