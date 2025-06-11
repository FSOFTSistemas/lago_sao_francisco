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
        Schema::create('mov_day_uses', function (Blueprint $table) {
            $table->id();
            $table->integer('quantidade');
            $table->unsignedBigInteger('dayuse_id');
            $table->unsignedBigInteger('item_dayuse_id');
            $table->foreign('dayuse_id')->references('id')->on('day_uses')->onDelete('cascade');
            $table->foreign('item_dayuse_id')->references('id')->on('itens_day_uses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mov_day_uses');
    }
};
