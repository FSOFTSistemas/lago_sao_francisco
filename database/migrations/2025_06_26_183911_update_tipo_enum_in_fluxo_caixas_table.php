<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTipoEnumInFluxoCaixasTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE fluxo_caixas MODIFY tipo ENUM('entrada', 'saida', 'abertura', 'fechamento') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE fluxo_caixas MODIFY tipo ENUM('entrada', 'saida') NOT NULL");
    }
}