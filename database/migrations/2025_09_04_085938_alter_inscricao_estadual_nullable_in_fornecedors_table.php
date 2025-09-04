<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fornecedors', function (Blueprint $table) {
            $table->dropColumn('inscricao_estadual');
        });

        Schema::table('fornecedors', function (Blueprint $table) {
            $table->string('inscricao_estadual')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fornecedors', function (Blueprint $table) {
            $table->dropColumn('inscricao_estadual');
        });

        Schema::table('fornecedors', function (Blueprint $table) {
            $table->string('inscricao_estadual');
        });
    }
};