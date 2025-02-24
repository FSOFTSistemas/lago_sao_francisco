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
        Schema::create('contas_correntes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('numero_conta');
            $table->unsignedBigInteger('banco_id');
            $table->string('titular')->nullable();
            $table->decimal('saldo', 15, 2)->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_correntes');
    }
};
