<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\DfeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SincronizarDfeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dfe:sincronizar {--empresa= : ID da empresa para sincronizar} {--force : Ignora intervalo antibloqueio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza documentos fiscais eletrônicos (DF-e) emitidos contra a empresa junto à SEFAZ';

    /**
     * Execute the console command.
     */
    public function handle(DfeService $dfeService): int
    {
        $empresaId = $this->option('empresa');
        $force = (bool) $this->option('force');

        $query = Empresa::query();
        if ($empresaId) {
            $query->where('id', $empresaId);
        }

        $empresas = $query->get();

        if ($empresas->isEmpty()) {
            $this->warn('Nenhuma empresa encontrada para sincronização DF-e.');
            return Command::SUCCESS;
        }

        $this->info("Iniciando sincronização DF-e para {$empresas->count()} empresa(s)...");

        foreach ($empresas as $empresa) {
            $this->info("----------------------------------------------------------------");
            $this->info("Empresa: [{$empresa->id}] {$empresa->razao_social} (CNPJ: {$empresa->cnpj})");

            $preferencia = $empresa->preferencia;
            $ultNsuInicial = $preferencia?->ult_nsu ?? '0';
            $this->line("NSU Inicial: {$ultNsuInicial}");

            $limiteIteracoes = 10; // Evita loop infinito
            $iteracao = 0;

            while ($iteracao < $limiteIteracoes) {
                $iteracao++;
                $this->line("Consultando lote {$iteracao} na SEFAZ...");

                $resultado = $dfeService->sincronizarLote($empresa, $force);

                if (!empty($resultado['bloqueado'])) {
                    $this->warn($resultado['mensagem'] ?? 'Consulta em espera por intervalo da SEFAZ.');
                    break;
                }

                if (!$resultado['sucesso']) {
                    $this->error("Erro: " . ($resultado['erro'] ?? 'Erro desconhecido ao consultar SEFAZ.'));
                    break;
                }

                $cStat = $resultado['cStat'] ?? '';

                if ($cStat === '137') {
                    $this->info("SEFAZ (137): Nenhum novo documento. NSU atual: {$resultado['ultNSU']}.");
                    break;
                }

                if ($cStat === '138') {
                    $qtd = $resultado['qtdProcessados'] ?? 0;
                    $this->info("SEFAZ (138): {$qtd} documento(s) processado(s). ultNSU: {$resultado['ultNSU']} | maxNSU: {$resultado['maxNSU']}.");

                    if (!empty($resultado['temMais'])) {
                        $this->line("Aguardando 2 segundos antes de buscar o próximo lote de NSU...");
                        sleep(2);
                    } else {
                        $this->info("Todos os lotes disponíveis até maxNSU foram sincronizados!");
                        break;
                    }
                }
            }
        }

        $this->info("----------------------------------------------------------------");
        $this->info("Rotina de sincronização DF-e finalizada com sucesso.");

        return Command::SUCCESS;
    }
}
