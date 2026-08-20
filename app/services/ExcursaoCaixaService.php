<?php

namespace App\Services;

use App\Models\Caixa;
use App\Models\Excursao;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use App\Models\RecebimentoExcursao;
use DomainException;
use Illuminate\Support\Facades\Auth;

class ExcursaoCaixaService
{
    public function __construct(private readonly CaixaService $caixaService) {}

    public function caixaAbertoDoUsuario(): Caixa
    {
        $usuario = Auth::user();

        if (! $usuario?->empresa_id) {
            throw new DomainException('Não foi possível identificar a empresa do usuário para registrar o pagamento.');
        }

        $caixa = Caixa::abertoHojePara($usuario->empresa_id, $usuario->id)->first();

        if (! $caixa) {
            throw new DomainException('Abra o seu caixa do dia antes de cadastrar pagamentos de excursão.');
        }

        return $caixa;
    }

    public function registrarRecebimento(RecebimentoExcursao $recebimento, Caixa $caixa): void
    {
        if ($recebimento->fluxo_caixa_id) {
            throw new DomainException('Este recebimento já possui uma movimentação de caixa.');
        }

        $formaPagamento = $recebimento->formaPagamento()->firstOrFail();
        if (str_contains(mb_strtolower($formaPagamento->descricao ?? ''), 'crediário')) {
            throw new DomainException('Crediário não pode ser usado como pagamento inicial da excursão.');
        }

        $movimentoDescricao = $formaPagamento->movimentoDescricao('venda');
        $movimentoId = Movimento::where('descricao', $movimentoDescricao)->value('id');
        if (! $movimentoId) {
            throw new DomainException("O movimento de caixa '{$movimentoDescricao}' não está cadastrado.");
        }

        $fluxo = $this->caixaService->inserirMovimentacao($caixa, [
            'descricao' => 'Pagamento de excursão #'.$recebimento->excursao_id,
            'valor' => $recebimento->valor,
            'valor_total' => $recebimento->valor,
            'tipo' => 'entrada',
            'movimento_id' => $movimentoId,
            'plano_de_conta_id' => $this->planoDeContaId($caixa),
        ]);

        $recebimento->updateQuietly(['fluxo_caixa_id' => $fluxo->id]);
    }

    public function cancelarRecebimentos(Excursao $excursao, Caixa $caixa): void
    {
        $excursao->loadMissing('recebimentos.formaPagamento');

        foreach ($excursao->recebimentos as $recebimento) {
            if (! $recebimento->fluxo_caixa_id || $recebimento->fluxo_cancelamento_id) {
                continue;
            }

            $movimentoDescricao = $recebimento->formaPagamento->movimentoDescricao('cancelamento');
            $movimentoId = Movimento::where('descricao', $movimentoDescricao)->value('id')
                ?? Movimento::where('descricao', 'cancelamento')->value('id');

            if (! $movimentoId) {
                throw new DomainException("O movimento de caixa '{$movimentoDescricao}' não está cadastrado.");
            }

            $fluxo = $this->caixaService->inserirMovimentacao($caixa, [
                'descricao' => 'Cancelamento de pagamento da excursão #'.$excursao->id,
                'valor' => $recebimento->valor,
                'valor_total' => $recebimento->valor,
                'tipo' => 'cancelamento',
                'movimento_id' => $movimentoId,
                'plano_de_conta_id' => $this->planoDeContaId($caixa),
            ]);

            $recebimento->updateQuietly(['fluxo_cancelamento_id' => $fluxo->id]);
        }
    }

    private function planoDeContaId(Caixa $caixa): int
    {
        $plano = PlanoDeConta::firstOrCreate(
            [
                'descricao' => 'Excursões',
                'tipo' => 'receita',
                'empresa_id' => $caixa->empresa_id,
            ],
            [
                'plano_de_conta_pai' => PlanoDeConta::idPorDescricao(
                    'Receitas Operacionais',
                    $caixa->empresa_id,
                    'receita',
                ),
            ],
        );

        return $plano->id;
    }
}
