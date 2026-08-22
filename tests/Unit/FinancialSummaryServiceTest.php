<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Services\Financeiro\FinancialSummaryCalculator;
use App\Services\Financeiro\FinancialSummaryRepository;
use App\Services\Financeiro\FinancialSummaryService;
use App\Support\CompanyContext;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FinancialSummaryServiceTest extends TestCase
{
    public function test_todas_as_consultas_recebem_a_empresa_do_contexto(): void
    {
        $context = new CompanyContext;
        $company = new Empresa;
        $company->id = 9;
        $context->set($company);

        $repository = $this->createMock(FinancialSummaryRepository::class);
        $repository->expects($this->once())
            ->method('receivables')
            ->with(9, $this->anything(), $this->anything())
            ->willReturn(new Collection);
        $repository->expects($this->once())
            ->method('payables')
            ->with(9, $this->anything(), $this->anything())
            ->willReturn(new Collection);
        $repository->expects($this->once())
            ->method('cashFlow')
            ->with(9, $this->anything(), $this->anything())
            ->willReturn(new Collection);

        $result = (new FinancialSummaryService(
            $context,
            $repository,
            new FinancialSummaryCalculator,
        ))->summarize('2026-08-01', '2026-08-31', '2026-08-22');

        $this->assertSame(9, $result['company_id']);
        $this->assertSame([
            'start' => '2026-08-01',
            'end' => '2026-08-31',
        ], $result['period']);
    }

    public function test_rejeita_periodo_invertido_antes_de_consultar_o_banco(): void
    {
        $context = new CompanyContext;
        $company = new Empresa;
        $company->id = 9;
        $context->set($company);

        $repository = $this->createMock(FinancialSummaryRepository::class);
        $repository->expects($this->never())->method('receivables');
        $repository->expects($this->never())->method('payables');
        $repository->expects($this->never())->method('cashFlow');

        $service = new FinancialSummaryService(
            $context,
            $repository,
            new FinancialSummaryCalculator,
        );

        $this->expectException(InvalidArgumentException::class);

        $service->summarize('2026-08-31', '2026-08-01');
    }
}
