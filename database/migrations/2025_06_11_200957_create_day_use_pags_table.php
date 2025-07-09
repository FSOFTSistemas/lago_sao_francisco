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
        Schema::create('day_use_pags', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor', 10, 2);
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->unsignedBigInteger('dayuse_id');
            $table->foreign('forma_pagamento_id')->references('id')->on('forma_pagamentos')->onDelete('cascade');
            $table->foreign('dayuse_id')->references('id')->on('day_uses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_use_pags');
    }
};
