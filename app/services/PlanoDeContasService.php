<?php

namespace App\services;

use App\Models\PlanoDeConta;
use App\Models\FluxoCaixa; // Garanta que o nome do Model está correto
use App\Models\ContasAPagar;
use App\Models\ContasAReceber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlanoDeContasService
{
    public function gerarRelatorioHierarquico(?int $empresaId = null, $dataInicio = null, $dataFim = null): array
    {
        $todasContas = PlanoDeConta::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id'))
            ->get();

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
        $idsDeMovimentoParaIgnorar = [17, 18, 19, 20]; // sangria, suprimento, abertura, fechamento

        $fluxoQuery = FluxoCaixa::query()
            ->select('plano_de_conta_id', DB::raw("SUM(CASE WHEN tipo = 'entrada' THEN valor WHEN tipo = 'saida' THEN -valor ELSE 0 END) as total"))
            ->whereNotNull('plano_de_conta_id')
            ->whereNotIn('movimento_id', $idsDeMovimentoParaIgnorar) // A CLÁUSULA CORRETA
            ->where('tipo', '!=', 'cancelamento')
            ->groupBy('plano_de_conta_id');
        $fluxoQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
        if ($dataInicio && $dataFim) $fluxoQuery->whereBetween('data', [$dataInicio, $dataFim]);

        foreach ($fluxoQuery->get() as $item) {
            $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) + $item->total;
        }

        $pagarQuery = ContasAPagar::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_pago) as total'))
            ->where('status', 'pago')->whereNotNull('plano_de_contas_id')->groupBy('plano_de_contas_id');
        $pagarQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
        if ($dataInicio && $dataFim) $pagarQuery->whereBetween('data_pagamento', [$dataInicio, $dataFim]);

        foreach ($pagarQuery->get() as $item) {
            $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) - $item->total;
        }

        $receberQuery = ContasAReceber::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_recebido) as total'))
            ->where('status', 'recebido')->whereNotNull('plano_de_contas_id')->groupBy('plano_de_contas_id');
        $receberQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));
        if ($dataInicio && $dataFim) $receberQuery->whereBetween('data_recebimento', [$dataInicio, $dataFim]);

        foreach ($receberQuery->get() as $item) {
            $totais[$item->plano_de_conta_id] = ($totais[$item->plano_de_conta_id] ?? 0) + $item->total;
        }
        
        return $totais;
    }
    
    private function construirArvore(Collection $contas): array
    {
        $arvore = [];
        $mapa = [];
        foreach ($contas as $conta) {
            $mapa[$conta->id] = ['model' => $conta, 'filhos' => [], 'total_cumulativo' => 0];
        }
        foreach ($mapa as $id => &$node) {
            $parentId = $node['model']->plano_de_conta_pai;
            if ($parentId !== null && isset($mapa[$parentId])) {
                $mapa[$parentId]['filhos'][] = &$node;
            } else {
                $arvore[] = &$node;
            }
        }
        return $arvore;
    }

    private function calcularTotaisCumulativos(array &$nodes, array $totaisIndividuais)
    {
        foreach ($nodes as &$node) {
            if (!empty($node['filhos'])) {
                $this->calcularTotaisCumulativos($node['filhos'], $totaisIndividuais);
            }
            $totalFilhos = collect($node['filhos'])->sum('total_cumulativo');
            $totalProprio = $totaisIndividuais[$node['model']->id] ?? 0;
            $node['total_cumulativo'] = $totalProprio + $totalFilhos;
        }
    }
}