<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AtualizarStatusContasAtrasadas;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agendamento do comando para rodar diariamente às 01:00
Schedule::command(AtualizarStatusContasAtrasadas::class)->dailyAt('01:00');
