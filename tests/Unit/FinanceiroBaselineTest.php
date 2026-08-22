<?php

namespace Tests\Unit;

use App\Models\ParcelaContasAPagar;
use App\Models\Transacao;
use App\Services\ContasService;
use PHPUnit\Framework\TestCase;

class FinanceiroBaselineTest extends TestCase
{
    public function test_parcela_aceita_os_dois_status_historicos_de_pagamento(): void
    {
        $parcela = new ParcelaContasAPagar;

        $parcela->status = 'pendente';
        $this->assertFalse($parcela->isPaga());

        $parcela->status = 'pago';
        $this->assertTrue($parcela->isPaga());

        $parcela->status = 'finalizado';
        $this->assertTrue($parcela->isPaga());
    }

    public function test_proximo_mes_preserva_o_comportamento_atual_de_datas(): void
    {
        $this->assertSame('2026-09-21', ContasService::proximoMes('2026-08-19'));
        $this->assertSame('2026-07-01', ContasService::proximoMes('2026-05-31'));
    }

    public function test_transacao_preserva_campos_e_conversoes_do_contrato_atual(): void
    {
        $transacao = new Transacao;

        $this->assertSame([
            'descricao',
            'status',
            'forma_pagamento_id',
            'categoria',
            'data_pagamento',
            'data_vencimento',
            'tipo',
            'valor',
            'observacoes',
            'reserva_id',
            'comprovante_path',
        ], $transacao->getFillable());

        $this->assertSame('boolean', $transacao->getCasts()['status']);
        $this->assertSame('date', $transacao->getCasts()['data_pagamento']);
        $this->assertSame('date', $transacao->getCasts()['data_vencimento']);
    }
}
