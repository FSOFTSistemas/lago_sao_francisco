<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCanceladoToSituacaoInReservasTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE reservas MODIFY situacao ENUM('pre-reserva', 'reserva', 'hospedado', 'bloqueado', 'finalizada', 'cancelado')");
    }

    public function down()
    {
        DB::statement("ALTER TABLE reservas MODIFY situacao ENUM('pre-reserva', 'reserva', 'hospedado', 'bloqueado', 'finalizada')");
    }
}
