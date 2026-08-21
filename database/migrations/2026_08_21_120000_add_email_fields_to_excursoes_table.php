<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->string('email_responsavel')->nullable()->after('telefone_responsavel');
            $table->timestamp('email_agendamento_enviado_em')->nullable()->after('email_responsavel');
            $table->timestamp('email_agendamento_tentado_em')->nullable()->after('email_agendamento_enviado_em');
            $table->unsignedInteger('email_agendamento_tentativas')->default(0)->after('email_agendamento_tentado_em');
            $table->text('email_agendamento_erro')->nullable()->after('email_agendamento_tentativas');
        });
    }

    public function down(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->dropColumn([
                'email_responsavel',
                'email_agendamento_enviado_em',
                'email_agendamento_tentado_em',
                'email_agendamento_tentativas',
                'email_agendamento_erro',
            ]);
        });
    }
};
