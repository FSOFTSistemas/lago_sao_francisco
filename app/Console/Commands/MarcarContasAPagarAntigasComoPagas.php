<?php

namespace App\Console\Commands;

use App\Models\ContasAPagar;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarcarContasAPagarAntigasComoPagas extends Command
{
    protected $signature = 'financeiro:marcar-contas-antigas-pagas
        {--data= : Data de corte no formato YYYY-MM-DD. Por padrão usa hoje.}
        {--dry-run : Mostra o que seria alterado sem gravar no banco.}';

    protected $description = 'Marca contas a pagar vencidas antes da data de corte como pagas e mantém hoje em diante pendente.';

    public function handle(): int
    {
        $dataCorte = $this->option('data')
            ? Carbon::parse($this->option('data'))->toDateString()
            : today()->toDateString();

        $contasAntigas = DB::table('contas_a_pagar')
            ->whereNull('total_parcelas')
            ->whereDate('data_vencimento', '<', $dataCorte)
            ->where(function ($query) {
                $query->where('status', '!=', 'pago')
                    ->orWhereNull('data_pagamento')
                    ->orWhereColumn('valor_pago', '<>', 'valor');
            });

        $parcelasAntigas = DB::table('parcelas_contas_a_pagar')
            ->whereDate('data_vencimento', '<', $dataCorte)
            ->where(function ($query) {
                $query->whereNotIn('status', ['pago', 'finalizado'])
                    ->orWhereNull('data_pagamento')
                    ->orWhereColumn('valor_pago', '<>', 'valor');
            });

        $parcelasSemStatusFuturas = DB::table('parcelas_contas_a_pagar')
            ->whereDate('data_vencimento', '>=', $dataCorte)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '');
            });

        $contasAntigasCount = (clone $contasAntigas)->count();
        $parcelasAntigasCount = (clone $parcelasAntigas)->count();
        $parcelasSemStatusFuturasCount = (clone $parcelasSemStatusFuturas)->count();

        $this->line("Data de corte: {$dataCorte}");
        $this->line("Contas nao parceladas antigas a marcar como pagas: {$contasAntigasCount}");
        $this->line("Parcelas antigas a marcar como pagas: {$parcelasAntigasCount}");
        $this->line("Parcelas de hoje em diante sem status a normalizar como pendente: {$parcelasSemStatusFuturasCount}");

        if ($this->option('dry-run')) {
            $this->warn('Simulacao concluida. Nenhum dado foi alterado.');

            return self::SUCCESS;
        }

        $contasRecalculadas = 0;

        DB::transaction(function () use ($contasAntigas, $parcelasAntigas, $parcelasSemStatusFuturas, &$contasRecalculadas) {
            $contasAntigas->update([
                'status' => 'pago',
                'valor_pago' => DB::raw('valor'),
                'data_pagamento' => DB::raw('COALESCE(data_pagamento, data_vencimento)'),
                'updated_at' => now(),
            ]);

            $parcelasAntigas->update([
                'status' => 'pago',
                'valor_pago' => DB::raw('valor'),
                'data_pagamento' => DB::raw('COALESCE(data_pagamento, data_vencimento)'),
                'updated_at' => now(),
            ]);

            $parcelasSemStatusFuturas->update([
                'status' => 'pendente',
                'valor_pago' => DB::raw('COALESCE(valor_pago, 0)'),
                'updated_at' => now(),
            ]);

            ContasAPagar::whereNotNull('total_parcelas')
                ->with('parcelas')
                ->chunkById(200, function ($contas) use (&$contasRecalculadas) {
                    foreach ($contas as $conta) {
                        $temPendentes = $conta->parcelas->contains(function ($parcela) {
                            return ! in_array($parcela->status, ['pago', 'finalizado'], true);
                        });

                        $conta->update([
                            'valor_pago' => $conta->parcelas->sum('valor_pago'),
                            'status' => $temPendentes ? 'pendente' : 'pago',
                            'data_pagamento' => $temPendentes ? null : $conta->parcelas->max('data_pagamento'),
                        ]);

                        $contasRecalculadas++;
                    }
                });
        });

        $this->info('Atualizacao concluida.');
        $this->line("Contas parceladas recalculadas: {$contasRecalculadas}");

        return self::SUCCESS;
    }
}
