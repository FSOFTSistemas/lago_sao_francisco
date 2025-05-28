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
        Schema::create('empresa_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('certificado_digital')->nullable();
            $table->integer('numero_ultima_nota')->nullable();
            $table->string('serie')->nullable();
            $table->string('cfop_padrao')->nullable();
            $table->string('regime_tributario')->nullable();
            $table->timestamps();

             $table->foreign('empresa_id')->references('id')->on('empresas')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_preferencias');
    }
};
