<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE excursoes MODIFY status ENUM('AGENDADO', 'EM_ANDAMENTO', 'REALIZADO', 'CANCELADO') NOT NULL DEFAULT 'AGENDADO'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('excursoes')
                ->where('status', 'EM_ANDAMENTO')
                ->update(['status' => 'AGENDADO']);

            DB::statement("ALTER TABLE excursoes MODIFY status ENUM('AGENDADO', 'REALIZADO', 'CANCELADO') NOT NULL DEFAULT 'AGENDADO'");
        }
    }
};
