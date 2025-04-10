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
        Schema::create('hospedes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('passaporte')->nullable();
            $table->date('nascimento')->nullable();
            $table->enum('sexo', ['masculino', 'feminino', 'outro'])->nullable();
            $table->string('profissao')->nullable();
            $table->text('observacao')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->foreignId('endereco_id')->nullable()->constrained()->onDelete('set null');
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospedes');
    }
};
