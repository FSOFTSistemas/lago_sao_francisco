<?php

use App\Console\Commands\AtualizarStatusContasAtrasadas;
use App\Console\Commands\EnviarNotificacoesDiarias;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendamento do comando para rodar diariamente às 01:00
Schedule::command(AtualizarStatusContasAtrasadas::class)->dailyAt('01:00');

// Notificações de contas a pagar vencendo e movimentação do dia
Schedule::command(EnviarNotificacoesDiarias::class)->dailyAt('06:00');
