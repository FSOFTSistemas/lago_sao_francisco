<?php

namespace App\Listeners;

use App\Models\Caixa;
use App\Notifications\CaixaNaoAbertoNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Notifications\DatabaseNotification;

class VerificarCaixaNoLogin
{
    public function handle(Login $event): void
    {
        $usuario = $event->user;

        if ($usuario->hasRole('Master')) {
            return;
        }

        $temCaixaAberto = Caixa::abertoHojePara($usuario->empresa_id, $usuario->id)->exists();

        if ($temCaixaAberto) {
            return;
        }

        $hoje = Carbon::today()->format('Y-m-d');

        $jaNotificado = DatabaseNotification::where('notifiable_type', get_class($usuario))
            ->where('notifiable_id', $usuario->id)
            ->where('type', CaixaNaoAbertoNotification::class)
            ->whereNull('read_at')
            ->where('data->data_referencia', $hoje)
            ->exists();

        if ($jaNotificado) {
            return;
        }

        $usuario->notify(new CaixaNaoAbertoNotification($hoje));
    }
}
