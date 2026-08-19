<?php

namespace Tests\Unit;

use App\Services\ExcursaoFinanceiroService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExcursaoFinanceiroServiceTest extends TestCase
{
    private ExcursaoFinanceiroService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ExcursaoFinanceiroService;
    }

    public function test_centraliza_todos_os_calculos_financeiros(): void
    {
        $resultado = $this->service->calcular([
            'qtd_pessoas' => 10,
            'valor_pessoa' => 99.99,
            'valor_almoco' => 25.50,
            'qtd_almoco' => 3,
            'acrescimo' => 50,
            'desconto' => 26.40,
            'percentual_comissao' => 10,
        ], [300, 250]);

        $this->assertSame(999.90, $resultado['valor_pessoas']);
        $this->assertSame(76.50, $resultado['total_almoco']);
        $this->assertSame(1076.40, $resultado['subtotal']);
        $this->assertSame(1100.0, $resultado['total']);
        $this->assertSame(99.99, $resultado['valor_comissao']);
        $this->assertSame(1000.01, $resultado['receita_liquida']);
        $this->assertSame(550.0, $resultado['valor_pago']);
        $this->assertSame(550.0, $resultado['valor_restante']);
        $this->assertFalse($resultado['quitada']);
    }

    public function test_considera_um_centavo_de_tolerancia_para_quitacao(): void
    {
        $resultado = $this->service->calcular([
            'qtd_pessoas' => 1,
            'valor_pessoa' => 100,
        ], [99.99]);

        $this->assertSame(0.01, $resultado['valor_restante']);
        $this->assertTrue($resultado['quitada']);
    }

    public function test_impede_total_negativo_ou_igual_a_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maior que zero');

        $this->service->calcular([
            'qtd_pessoas' => 1,
            'valor_pessoa' => 0,
        ]);
    }

    public function test_impede_desconto_maior_que_subtotal_mais_acrescimo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('desconto não pode ser maior');

        $this->service->calcular([
            'qtd_pessoas' => 1,
            'valor_pessoa' => 100,
            'acrescimo' => 10,
            'desconto' => 110.01,
        ]);
    }

    public function test_arredonda_comissao_para_duas_casas(): void
    {
        $resultado = $this->service->calcular([
            'qtd_pessoas' => 3,
            'valor_pessoa' => 33.33,
            'percentual_comissao' => 10,
        ]);

        $this->assertSame(99.99, $resultado['valor_pessoas']);
        $this->assertSame(10.0, $resultado['valor_comissao']);
    }
}
