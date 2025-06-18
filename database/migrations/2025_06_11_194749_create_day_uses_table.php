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
        Schema::create('day_uses', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->decimal('total', 10, 2);
            $table->decimal('acrescimo', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('vendedor_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('vendedors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_uses');
    }
};
