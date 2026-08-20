<?php

namespace App\Services;

use App\Models\Caixa;
use App\Models\FluxoCaixa;
use App\Models\Log as LogSistema;
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

        $this->registrarLogMovimentacao(
            $recebimento,
            $fluxo,
            $caixa,
            'Recebimento de excursão lançado no fluxo de caixa.',
        );
    }

    private function registrarLogMovimentacao(
        RecebimentoExcursao $recebimento,
        FluxoCaixa $fluxo,
        Caixa $caixa,
        string $mensagem,
    ): void {
        $formaPagamento = $recebimento->relationLoaded('formaPagamento')
            ? $recebimento->formaPagamento
            : $recebimento->formaPagamento()->first();

        LogSistema::create([
            'tipo_acao' => 'Criou',
            'descricao' => $mensagem
                .' | Excursão ID: '.$recebimento->excursao_id
                .' | Recebimento ID: '.$recebimento->id
                .' | Fluxo ID: '.$fluxo->id
                .' | Empresa: '.$caixa->empresa_id
                .' | Caixa: '.$caixa->id
                .' | Data: '.$fluxo->data
                .' | Valor: R$ '.number_format((float) $fluxo->valor, 2, ',', '.')
                .' | Forma de pagamento: '.($formaPagamento?->descricao ?? 'Não informada')
                .' | Plano de conta ID: '.$fluxo->plano_de_conta_id
                .' | Movimento ID: '.$fluxo->movimento_id,
            'usuario_id' => Auth::id(),
            'data_hora' => now(),
        ]);
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
