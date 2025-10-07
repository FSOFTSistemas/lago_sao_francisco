<?php

namespace App\services;

use App\Models\ContasAPagar;
use App\Models\FluxoCaixa;
use App\Models\PlanoDeConta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanoDeContasService
{
    public function gerarRelatorioHierarquico(?int $empresaId = null, $dataInicio = null, $dataFim = null): array
    {
        $query = PlanoDeConta::query();
        $query->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        });
        $todasContas = $query->get();

        if ($todasContas->isEmpty()) {
            return [];
        }

        $totaisIndividuais = $this->calcularTotaisIndividuais($empresaId, $dataInicio, $dataFim);
        $arvore = $this->construirArvore($todasContas);
        $this->calcularTotaisCumulativos($arvore, $totaisIndividuais);
        return $arvore;
    }

    private function calcularTotaisIndividuais(?int $empresaId = null, $dataInicio = null, $dataFim): array
{
    $totais = [];

    // IDs dos movimentos que são puramente operacionais e devem ser ignorados no relatório.
    // Baseado no seu print: sangria(17), suprimento(18), abertura de caixa(19), fechamento de caixa(20)
    $idsDeMovimentoParaIgnorar = [17, 18, 19, 20];

    // --- Fluxo de Caixas ---
    $fluxoQuery = FluxoCaixa::query()
        ->select('plano_de_conta_id', DB::raw("SUM(CASE WHEN tipo = 'entrada' THEN valor WHEN tipo = 'saida' THEN -valor ELSE 0 END) as total"))
        ->whereNotNull('plano_de_conta_id')
        ->whereNotIn('movimento_id', $idsDeMovimentoParaIgnorar) // <-- LÓGICA AVANÇADA AQUI
        ->where('tipo', '!=', 'cancelamento') // Ignora também os cancelamentos
        ->groupBy('plano_de_conta_id');
    
    $fluxoQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
    if ($dataInicio && $dataFim) $fluxoQuery->whereBetween('data', [$dataInicio, $dataFim]);
    
    foreach ($fluxoQuery->get() as $item) {
        $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) + $item->total;
    }

    // --- Contas a Pagar ---
    $pagarQuery = ContasAPagar::query()
        ->select('plano_de_contas_id', DB::raw('SUM(valor_pago) as total'))
        ->where('status', 'pago')->whereNotNull('plano_de_contas_id')->groupBy('plano_de_contas_id');
    $pagarQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
    if ($dataInicio && $dataFim) $pagarQuery->whereBetween('data_pagamento', [$dataInicio, $dataFim]);

    foreach ($pagarQuery->get() as $item) {
        $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) - $item->total;
    }

    // --- Contas a Receber ---
    $receberQuery = \App\Models\ContasAReceber::query()
        ->select('plano_de_contas_id', DB::raw('SUM(valor_recebido) as total'))
        ->where('status', 'recebido') // Assumindo 'recebido' como status.
        ->whereNotNull('plano_de_contas_id')->groupBy('plano_de_contas_id');
    $receberQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
    if ($dataInicio && $dataFim) $receberQuery->whereBetween('data_recebimento', [$dataInicio, $dataFim]);

    foreach ($receberQuery->get() as $item) {
        $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) + $item->total;
    }
    
    return $totais;
}

    /**
     * Constrói a árvore a partir de uma lista de contas. VERSÃO FINAL E DEFINITIVA.
     */
    private function construirArvore(Collection $contas): array
    {
        $arvore = [];
        $mapa = [];

        // 1. Prepara todos os nós no mapa, já com a estrutura final.
        foreach ($contas as $conta) {
            $mapa[$conta->id] = [
                'model' => $conta,
                'filhos' => [],
                'total_cumulativo' => 0 // Inicializa o total
            ];
        }

        // 2. Itera sobre o mapa para conectar pais e filhos.
        foreach ($mapa as $id => &$node) {
            $parentId = $node['model']->plano_de_conta_pai;

            if ($parentId !== null && isset($mapa[$parentId])) {
                // Se tem um pai válido, anexa este nó como filho do pai.
                $mapa[$parentId]['filhos'][] = &$node;
            } else {
                // Se não tem pai, é um nó raiz.
                $arvore[] = &$node;
            }
        }
        
        return $arvore;
    }

    private function calcularTotaisCumulativos(array &$nodes, array $totaisIndividuais)
    {
        foreach ($nodes as &$node) {
            // Chama a recursão para os filhos primeiro
            if (!empty($node['filhos'])) {
                $this->calcularTotaisCumulativos($node['filhos'], $totaisIndividuais);
            }

            // Soma o total dos filhos que já foi calculado
            $totalFilhos = collect($node['filhos'])->sum('total_cumulativo');
            
            // Pega o total individual deste nó
            $totalProprio = $totaisIndividuais[$node['model']->id] ?? 0;

            // O total cumulativo é a soma do seu próprio total mais o de todos os seus filhos
            $node['total_cumulativo'] = $totalProprio + $totalFilhos;
        }
    }
}