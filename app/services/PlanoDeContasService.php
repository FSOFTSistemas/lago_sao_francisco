<?php

namespace App\Services;

use App\Models\PlanoDeConta;
use App\Models\FluxoCaixa;
use App\Models\ContasAPagar;
use App\Models\ContasAReceber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanoDeContasService
{
    /**
     * Gera a estrutura de árvore do plano de contas com totais cumulativos.
     *
     * @param int $empresaId
     * @param string|null $dataInicio
     * @param string|null $dataFim
     * @return array
     */
    public function gerarRelatorioHierarquico(int $empresaId, $dataInicio = null, $dataFim = null): array
    {
        // 1. Buscar todas as contas da empresa
        $todasContas = PlanoDeConta::daEmpresa($empresaId)->get();

        // 2. Calcular os totais diretos para cada conta
        $totaisIndividuais = $this->calcularTotaisIndividuais($empresaId, $dataInicio, $dataFim);

        // 3. Montar a estrutura da árvore
        $arvore = $this->construirArvore($todasContas);

        // 4. Calcular os totais cumulativos (soma dos filhos)
        $this->calcularTotaisCumulativos($arvore, $totaisIndividuais);

        return $arvore;
    }

    /**
     * Calcula os totais lançados diretamente em cada plano de conta.
     */
    private function calcularTotaisIndividuais(int $empresaId, $dataInicio, $dataFim): array
    {
        $totais = [];

        // Fluxo de Caixa (Entradas - Saídas)
        $fluxoQuery = FluxoCaixa::where('empresa_id', $empresaId)
            ->select(
                'plano_de_conta_id',
                DB::raw("SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas"),
                DB::raw("SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas")
            )
            ->whereNotNull('plano_de_conta_id')
            ->groupBy('plano_de_conta_id');

        // Contas a Pagar (Saídas)
        $pagarQuery = ContasAPagar::daEmpresa($empresaId)
            ->select('plano_de_contas_id', DB::raw('SUM(valor_pago) as total_pago'))
            ->where('status', 'finalizado') // Considerar apenas o que foi pago
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');

        // Contas a Receber (Entradas)
        $receberQuery = ContasAReceber::where('empresa_id', $empresaId)
            ->select('plano_de_contas_id', DB::raw('SUM(valor_recebido) as total_recebido'))
            ->where('status', 'finalizado') // Considerar apenas o que foi recebido
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');

        // Filtros de data (se fornecidos)
        if ($dataInicio && $dataFim) {
            $fluxoQuery->whereBetween('data', [$dataInicio, $dataFim]);
            $pagarQuery->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
            $receberQuery->whereBetween('data_recebimento', [$dataInicio, $dataFim]);
        }

        $fluxos = $fluxoQuery->get();
        foreach ($fluxos as $fluxo) {
            $totais[$fluxo->plano_de_conta_id] = ($totais[$fluxo->plano_de_conta_id] ?? 0) + $fluxo->entradas - $fluxo->saidas;
        }

        $pagamentos = $pagarQuery->get();
        foreach ($pagamentos as $pagamento) {
            $totais[$pagamento->plano_de_contas_id] = ($totais[$pagamento->plano_de_contas_id] ?? 0) - $pagamento->total_pago;
        }
        
        $recebimentos = $receberQuery->get();
        foreach ($recebimentos as $recebimento) {
            $totais[$recebimento->plano_de_contas_id] = ($totais[$recebimento->plano_de_contas_id] ?? 0) + $recebimento->total_recebido;
        }

        return $totais;
    }

    /**
     * Constrói a árvore a partir de uma lista de contas.
     */
    private function construirArvore(Collection $contas): array
    {
        $mapaContas = [];
        // Criamos um "nó" para cada conta, que é um array contendo o modelo e um espaço para os filhos.
        foreach ($contas as $conta) {
            $mapaContas[$conta->id] = [
                'model' => $conta,
                'filhos' => []
            ];
        }

        $arvore = [];
        foreach ($mapaContas as $id => &$node) {
            $conta = $node['model'];
            if ($conta->plano_de_conta_pai && isset($mapaContas[$conta->plano_de_conta_pai])) {
                // Adicionamos o nó atual ao array 'filhos' do seu pai.
                $mapaContas[$conta->plano_de_conta_pai]['filhos'][] = &$node;
            } else {
                $arvore[] = &$node;
            }
        }
        return $arvore;
    }

    /**
     * CALCULAR TOTAIS CUMULATIVOS - MÉTODO CORRIGIDO
     */
    private function calcularTotaisCumulativos(array &$nodes, array $totaisIndividuais)
    {
        foreach ($nodes as &$node) { // Usamos referência '&' para modificar o array original
            if (!empty($node['filhos'])) {
                $this->calcularTotaisCumulativos($node['filhos'], $totaisIndividuais);
            }

            $totalProprio = $totaisIndividuais[$node['model']->id] ?? 0;
            
            // Somamos os totais que já foram calculados para os filhos.
            $totalFilhos = collect($node['filhos'])->sum('total_cumulativo');
            
            // Criamos a propriedade 'total_cumulativo' no nosso array de nó.
            $node['total_cumulativo'] = $totalProprio + $totalFilhos;
        }
    }
}