<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeyVendedorIdInDayUsesTable extends Migration
{
    public function up()
    {
        Schema::table('day_uses', function (Blueprint $table) {
            // Remove a foreign key antiga
            $table->dropForeign(['vendedor_id']);

            // Adiciona a nova foreign key apontando para 'funcionarios'
            $table->foreign('vendedor_id')
                ->references('id')
                ->on('funcionarios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('day_uses', function (Blueprint $table) {
            $table->dropForeign(['vendedor_id']);

            $table->foreign('vendedor_id')
                ->references('id')
                ->on('vendedors')
                ->onDelete('cascade');
        });
    }
}
