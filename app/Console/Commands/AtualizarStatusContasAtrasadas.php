<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContasAReceber;
use Carbon\Carbon;

class AtualizarStatusContasAtrasadas extends Command
{
    protected $signature = 'app:atualizar-status-contas-atrasadas';
    protected $description = 'Atualiza o status das contas a receber vencidas para "atrasado"';

    public function handle()
    {
        $qtd = ContasAReceber::where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now())
            ->update(['status' => 'atrasado']);

        $this->info("{$qtd} contas atualizadas para 'atrasado'.");
    }
}
