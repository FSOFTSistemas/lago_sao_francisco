<?php

namespace App\Console\Commands;

use App\Models\ContasAPagar;
use App\Models\Reserva;
use App\Models\User;
use App\Notifications\ContaAPagarVencendoNotification;
use App\Notifications\MovimentacaoHotelHojeNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification as NotificationModel;

class EnviarNotificacoesDiarias extends Command
{
    protected $signature = 'app:enviar-notificacoes-diarias';

    protected $description = 'Notifica contas a pagar vencendo e a movimentação do dia (check-ins/check-outs)';

    public function handle()
    {
        $this->notificarContasAPagarVencendo();
        $this->notificarMovimentacaoHoje();
    }

    private function notificarContasAPagarVencendo(): void
    {
        $hoje = Carbon::today();

        $contas = ContasAPagar::where(function ($q) use ($hoje) {
            $q->where('status', 'pendente')->whereDate('data_vencimento', '<=', $hoje);
        })->orWhereHas('parcelas', function ($q) use ($hoje) {
            $q->where('status', 'pendente')->whereDate('data_vencimento', '<=', $hoje);
        })->get();

        $enviadas = 0;

        foreach ($contas as $conta) {
            $usuarios = User::role(['Master', 'financeiro'])
                ->where('empresa_id', $conta->empresa_id)
                ->get();

            foreach ($usuarios as $usuario) {
                if ($this->jaTemNaoLida($usuario, ContaAPagarVencendoNotification::class, 'conta_id', $conta->id)) {
                    continue;
                }

                $usuario->notify(new ContaAPagarVencendoNotification($conta));
                $enviadas++;
            }
        }

        $this->info("{$enviadas} notificações de contas a pagar enviadas.");
    }

    private function notificarMovimentacaoHoje(): void
    {
        $hoje = Carbon::today()->format('Y-m-d');
        $situacoesValidas = ['reserva', 'hospedado', 'finalizada'];

        $checkins = Reserva::whereIn('situacao', $situacoesValidas)->where('data_checkin', $hoje)->count();
        $checkouts = Reserva::whereIn('situacao', $situacoesValidas)->where('data_checkout', $hoje)->count();

        if ($checkins === 0 && $checkouts === 0) {
            $this->info('Sem movimentação hoje, nenhuma notificação enviada.');

            return;
        }

        $enviadas = 0;

        foreach (User::all() as $usuario) {
            if ($this->jaTemNaoLida($usuario, MovimentacaoHotelHojeNotification::class, 'data_referencia', $hoje)) {
                continue;
            }

            $usuario->notify(new MovimentacaoHotelHojeNotification($checkins, $checkouts, $hoje));
            $enviadas++;
        }

        $this->info("{$enviadas} notificações de movimentação enviadas.");
    }

    private function jaTemNaoLida(User $usuario, string $tipo, string $chave, $valor): bool
    {
        return NotificationModel::where('notifiable_type', User::class)
            ->where('notifiable_id', $usuario->id)
            ->where('type', $tipo)
            ->whereNull('read_at')
            ->where('data->'.$chave, $valor)
            ->exists();
    }
}
