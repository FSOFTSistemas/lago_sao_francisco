<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CaixaNaoAbertoNotification extends Notification
{
    public function __construct(protected string $data) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'titulo' => 'Caixa não aberto',
            'mensagem' => 'Você ainda não abriu o caixa hoje.',
            'icone' => 'fas fa-cash-register',
            'cor' => 'danger',
            'url' => route('fluxoCaixa.index'),
            'data_referencia' => $this->data,
        ];
    }
}
