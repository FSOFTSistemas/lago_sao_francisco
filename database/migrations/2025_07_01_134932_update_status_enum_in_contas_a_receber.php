<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateStatusEnumInContasAReceber extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE contas_a_receber MODIFY COLUMN status ENUM('pendente', 'finalizado', 'atrasado') DEFAULT 'pendente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contas_a_receber MODIFY COLUMN status ENUM('pendente', 'finalizado') DEFAULT 'pendente'");
    }
}