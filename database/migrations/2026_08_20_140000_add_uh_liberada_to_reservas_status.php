<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE reservas
                MODIFY situacao ENUM(
                    'pre-reserva',
                    'reserva',
                    'hospedado',
                    'bloqueado',
                    'finalizada',
                    'cancelado',
                    'noshow',
                    'uh_liberada'
                )
            ");
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->timestamp('uh_liberada_em')->nullable()->after('hora_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('uh_liberada_em');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('reservas')
                ->where('situacao', 'uh_liberada')
                ->update(['situacao' => 'finalizada']);

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
    }
};
