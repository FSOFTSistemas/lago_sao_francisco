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
    Schema::create('logs', function (Blueprint $table) {
        $table->id();
        $table->enum('tipo_acao', ['Criou', 'Vizualizou', 'Atualizou', 'Excluiu']); 
        $table->text('descricao'); 
        $table->timestamp('data_hora')->useCurrent();
        $table->unsignedBigInteger('usuario_id');
        $table->timestamps();

        // Caso queira chave estrangeira para usuário
        $table->foreign('usuario_id')->references('id')->on('users');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
