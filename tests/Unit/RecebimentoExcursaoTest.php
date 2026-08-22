<?php

namespace Tests\Unit;

use App\Models\Excursao;
use App\Models\FormaPagamento;
use App\Models\RecebimentoExcursao;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class RecebimentoExcursaoTest extends TestCase
{
    public function test_possui_campos_casts_e_relacionamentos_esperados(): void
    {
        $recebimento = new RecebimentoExcursao;

        $this->assertSame([
            'excursao_id',
            'data_recebimento',
            'valor',
            'forma_pagamento_id',
            'fluxo_caixa_id',
            'fluxo_cancelamento_id',
            'comprovante_path',
        ], $recebimento->getFillable());
        $this->assertSame('date', $recebimento->getCasts()['data_recebimento']);
        $this->assertSame('decimal:2', $recebimento->getCasts()['valor']);
        $this->assertInstanceOf(BelongsTo::class, $recebimento->excursao());
        $this->assertInstanceOf(BelongsTo::class, $recebimento->formaPagamento());
        $this->assertSame(Excursao::class, $recebimento->excursao()->getRelated()::class);
        $this->assertSame(FormaPagamento::class, $recebimento->formaPagamento()->getRelated()::class);
    }

    public function test_permite_exclusao_se_restarem_pelo_menos_cinquenta_por_cento_pagos(): void
    {
        $recebimento = $this->criarCenarioParaExclusao(500);

        $this->assertTrue($recebimento->podeSerExcluido());
        $this->assertNull($recebimento->motivoBloqueioExclusao());
    }

    public function test_bloqueia_exclusao_se_pagamento_ficar_abaixo_de_cinquenta_por_cento(): void
    {
        $recebimento = $this->criarCenarioParaExclusao(499.98);

        $this->assertFalse($recebimento->podeSerExcluido());
        $this->assertStringContainsString('abaixo de 50%', $recebimento->motivoBloqueioExclusao());
    }

    public function test_bloqueia_exclusao_quando_excursao_nao_esta_agendada(): void
    {
        $recebimento = $this->criarCenarioParaExclusao(500, Excursao::STATUS_EM_ANDAMENTO);

        $this->assertFalse($recebimento->podeSerExcluido());
        $this->assertStringContainsString('estiver agendada', $recebimento->motivoBloqueioExclusao());
    }

    public function test_impede_edicao_de_um_recebimento_existente(): void
    {
        $recebimento = new RecebimentoExcursao;
        $recebimento->setRawAttributes(['id' => 1, 'valor' => '100.00'], true);
        $recebimento->exists = true;
        $recebimento->valor = '200.00';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('não podem ser editados');

        $recebimento->save();
    }

    private function criarCenarioParaExclusao(
        float $valorOutroRecebimento,
        string $status = Excursao::STATUS_AGENDADO,
    ): RecebimentoExcursao {
        $excursao = new Excursao([
            'status' => $status,
            'total' => 1000,
        ]);

        $recebimento = new RecebimentoExcursao(['valor' => '100.00']);
        $recebimento->id = 1;
        $outroRecebimento = new RecebimentoExcursao([
            'valor' => number_format($valorOutroRecebimento, 2, '.', ''),
        ]);
        $outroRecebimento->id = 2;

        $excursao->setRelation('recebimentos', new Collection([
            $recebimento,
            $outroRecebimento,
        ]));
        $recebimento->setRelation('excursao', $excursao);

        return $recebimento;
    }
}
