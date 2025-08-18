<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNoshowToSituacaoInReservasTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE reservas 
            MODIFY situacao ENUM(
                'pre-reserva', 
                'reserva', 
                'hospedado', 
                'bloqueado', 
                'finalizada', 
                'cancelado',
                'noshow'
            )
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE reservas 
            MODIFY situacao ENUM(
                'pre-reserva', 
                'reserva', 
                'hospedado', 
                'bloqueado', 
                'finalizada',
                'cancelado'
            )
        ");
    }
}