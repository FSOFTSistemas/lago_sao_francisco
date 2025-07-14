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
        Schema::create('day_use_souvenir', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dayuse_id');
            $table->foreign('dayuse_id')
                ->references('id')
                ->on('day_uses') 
                ->onDelete('cascade');
            $table->unsignedBigInteger('souvenir_id');
            $table->foreign('souvenir_id')
                ->references('id')
                ->on('souvenirs') 
                ->onDelete('cascade');
            $table->integer('quantidade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_use_souvenir');
    }
};
