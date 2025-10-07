<?php

namespace App\services; // Corrigido para 'services' minúsculo como você mencionou

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
     * O parâmetro $empresaId agora é opcional.
     */
    public function gerarRelatorioHierarquico(?int $empresaId = null, $dataInicio = null, $dataFim = null): array
    {
        // 1. Buscar contas. Se $empresaId for nulo, busca todas.
        $query = PlanoDeConta::query();
        $query->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        });
        $todasContas = $query->get();

        // 2. Calcular os totais. Se $empresaId for nulo, calcula o total de tudo.
        $totaisIndividuais = $this->calcularTotaisIndividuais($empresaId, $dataInicio, $dataFim);

        // 3. Montar a estrutura da árvore (sem alterações)
        $arvore = $this->construirArvore($todasContas);

        // 4. Calcular os totais cumulativos (sem alterações)
        $this->calcularTotaisCumulativos($arvore, $totaisIndividuais);

        return $arvore;
    }

    /**
     * Calcula os totais lançados diretamente em cada plano de conta.
     * O parâmetro $empresaId agora é opcional.
     */
    private function calcularTotaisIndividuais(?int $empresaId = null, $dataInicio, $dataFim): array
    {
        $totais = [];

        // --- Fluxo de Caixa ---
        $fluxoQuery = FluxoCaixa::query()
            ->select(
                'plano_de_conta_id',
                DB::raw("SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as entradas"),
                DB::raw("SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) as saidas")
            )
            ->whereNotNull('plano_de_conta_id')
            ->groupBy('plano_de_conta_id');
        
        // Aplica o filtro de empresa APENAS se um ID for fornecido
        $fluxoQuery->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId);
        });

        // --- Contas a Pagar ---
        $pagarQuery = ContasAPagar::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_pago) as total_pago'))
            ->where('status', 'finalizado')
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');
        
        $pagarQuery->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId);
        });

        // --- Contas a Receber ---
        $receberQuery = ContasAReceber::query()
            ->select('plano_de_contas_id', DB::raw('SUM(valor_recebido) as total_recebido'))
            ->where('status', 'finalizado')
            ->whereNotNull('plano_de_contas_id')
            ->groupBy('plano_de_contas_id');
            
        $receberQuery->when($empresaId, function ($q) use ($empresaId) {
            return $q->where('empresa_id', $empresaId);
        });

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
    
    // Os métodos construirArvore e calcularTotaisCumulativos não precisam de nenhuma alteração.
    // Copie-os da sua versão anterior que já estava funcionando.
    private function construirArvore(Collection $contas): array
    {
        $mapaContas = [];
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