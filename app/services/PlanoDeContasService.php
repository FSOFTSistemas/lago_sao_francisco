<?php

namespace App\services;

use App\Models\PlanoDeConta;
use App\Models\FluxoCaixa;
use App\Models\ContasAPagar;
use App\Models\ContasAReceber;
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

        $totaisIndividuais = $this->calcularTotaisIndividuais($empresaId, $dataInicio, $dataFim);
        $arvore = $this->construirArvore($todasContas);
        $this->calcularTotaisCumulativos($arvore, $totaisIndividuais);
        return $arvore;
    }

    private function calcularTotaisIndividuais(?int $empresaId = null, $dataInicio, $dataFim): array
    {
        $totais = [];

        // --- Fluxo de Caixas --- (Já está correto)
        $fluxoQuery = FluxoCaixa::query() // Ajustado para o nome correto do Model se necessário
            ->select(
                'plano_de_conta_id',
                DB::raw("SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas"),
                DB::raw("SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas")
            )
            ->whereNotNull('plano_de_conta_id')
            ->groupBy('plano_de_conta_id');
        $fluxoQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));

        // --- Contas a Pagar ---
        $pagarQuery = ContasAPagar::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_pago) as total_pago'))
            ->where('status', 'pago') // <<< --- CORREÇÃO APLICADA AQUI ---
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');
        $pagarQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));

        // --- Contas a Receber ---
        // Vou assumir que o status para recebido seria 'recebido'. Se for diferente, basta ajustar aqui.
        $receberQuery = ContasAReceber::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_recebido) as total_recebido'))
            ->where('status', 'recebido') // Assumindo 'recebido' como status para receitas.
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');
        $receberQuery->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId));

        // Filtros de data
        if ($dataInicio && $dataFim) {
            $fluxoQuery->whereBetween('data', [$dataInicio, $dataFim]);
            $pagarQuery->whereBetween('data_pagamento', [$dataInicio, $dataFim]);
            $receberQuery->whereBetween('data_recebimento', [$dataInicio, $dataFim]);
        }
        
        // Processamento dos totais (sem alterações)
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
    
    // Métodos construirArvore e calcularTotaisCumulativos (sem alterações)
    private function construirArvore(Collection $contas): array
    {
        $mapaContas = [];
        foreach ($contas as $conta) {
            $mapaContas[$conta->id] = ['model' => $conta, 'filhos' => []];
        }
        $arvore = [];
        foreach ($mapaContas as $id => &$node) {
            $conta = $node['model'];
            if ($conta->plano_de_conta_pai && isset($mapaContas[$conta->plano_de_conta_pai])) {
                $mapaContas[$conta->plano_de_conta_pai]['filhos'][] = &$node;
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
            $totalProprio = $totaisIndividuais[$node['model']->id] ?? 0;
            $totalFilhos = collect($node['filhos'])->sum('total_cumulativo');
            $node['total_cumulativo'] = $totalProprio + $totalFilhos;
        }
    }
}