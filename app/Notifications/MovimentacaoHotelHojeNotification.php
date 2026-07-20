<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class MovimentacaoHotelHojeNotification extends Notification
{
    public function __construct(protected int $checkins, protected int $checkouts, protected string $data) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'titulo' => 'Movimentação de hoje',
            'mensagem' => "{$this->checkins} check-in(s) e {$this->checkouts} check-out(s) hoje.",
            'icone' => 'fas fa-calendar-day',
            'cor' => 'info',
            'url' => route('mapa.index'),
            'data_referencia' => $this->data,
        ];
    }
}
