<?php

namespace App\Notifications;

use App\Models\ContasAPagar;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;

class ContaAPagarVencendoNotification extends Notification
{
    public function __construct(protected ContasAPagar $conta) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $vencida = Carbon::parse($this->conta->data_vencimento)->isPast();

        return [
            'titulo' => $vencida ? 'Conta a pagar atrasada' : 'Conta a pagar vence hoje',
            'mensagem' => $this->conta->descricao.' — R$ '.number_format($this->conta->valor, 2, ',', '.'),
            'icone' => 'fas fa-file-invoice-dollar',
            'cor' => $vencida ? 'danger' : 'warning',
            'url' => route('contasAPagar.edit', $this->conta->id),
            'conta_id' => $this->conta->id,
        ];
    }
}
