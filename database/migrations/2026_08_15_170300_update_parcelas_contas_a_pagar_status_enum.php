<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE parcelas_contas_a_pagar MODIFY COLUMN status ENUM('pendente', 'pago', 'finalizado') DEFAULT 'pendente'");
    }

    public function down(): void
    {
        DB::statement("UPDATE parcelas_contas_a_pagar SET status = 'finalizado' WHERE status = 'pago'");
        DB::statement("ALTER TABLE parcelas_contas_a_pagar MODIFY COLUMN status ENUM('pendente', 'finalizado') DEFAULT 'pendente'");
    }
};
