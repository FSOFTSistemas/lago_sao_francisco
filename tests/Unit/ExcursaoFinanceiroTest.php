<?php

namespace Tests\Unit;

use App\Models\Excursao;
use App\Models\RecebimentoExcursao;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class ExcursaoFinanceiroTest extends TestCase
{
    public function test_calcula_os_valores_financeiros_da_excursao(): void
    {
        $excursao = $this->criarExcursaoComRecebimentos([300, 200]);

        $this->assertSame(1000.0, $excursao->valor_pessoas);
        $this->assertSame(100.0, $excursao->valor_comissao);
        $this->assertSame(500.0, $excursao->valor_pago);
        $this->assertSame(600.0, $excursao->valor_restante);
        $this->assertSame(1000.0, $excursao->receita_liquida);
        $this->assertSame(45.45, $excursao->percentual_pago);
    }

    public function test_identifica_pagamento_minimo_e_quitacao(): void
    {
        $metadePaga = $this->criarExcursaoComRecebimentos([550]);
        $quitada = $this->criarExcursaoComRecebimentos([1100]);

        $this->assertTrue($metadePaga->pagamento_minimo_atingido);
        $this->assertFalse($metadePaga->quitada);
        $this->assertTrue($quitada->pagamento_minimo_atingido);
        $this->assertTrue($quitada->quitada);
        $this->assertSame(0.0, $quitada->valor_restante);
        $this->assertSame(100.0, $quitada->percentual_pago);
    }

    public function test_considera_tolerancia_de_um_centavo_na_quitacao(): void
    {
        $excursao = $this->criarExcursaoComRecebimentos([1099.99]);

        $this->assertTrue($excursao->quitada);
        $this->assertSame(0.01, $excursao->valor_restante);
    }

    /**
     * @param  array<int, float|int>  $recebimentos
     */
    private function criarExcursaoComRecebimentos(array $recebimentos): Excursao
    {
        $excursao = new Excursao([
            'qtd_pessoas' => 10,
            'valor_pessoa' => 100,
            'percentual_comissao' => 10,
            'acrescimo' => 100,
            'subtotal' => 1100,
            'total' => 1100,
        ]);

        $excursao->setRelation('recebimentos', new Collection(array_map(
            fn ($valor) => new RecebimentoExcursao([
                'valor' => number_format((float) $valor, 2, '.', ''),
            ]),
            $recebimentos,
        )));

        return $excursao;
    }
}
