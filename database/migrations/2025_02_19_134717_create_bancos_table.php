<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bancos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('agencia')->nullable();
            $table->string('numero_banco')->nullable();
            $table->string('numero_conta')->nullable();
            $table->string('digito_numero')->nullable();
            $table->string('digito_agencia')->nullable();
            $table->string('digito_conta')->nullable();
            $table->string('agencia_uf', 2)->nullable();
            $table->string('agencia_cidade')->nullable();
            $table->decimal('taxa', 10, 2)->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bancos');
    }
};
