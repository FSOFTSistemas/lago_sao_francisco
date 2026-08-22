<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Support\CompanyContext;
use LogicException;
use PHPUnit\Framework\TestCase;

class CompanyContextTest extends TestCase
{
    public function test_expoe_a_empresa_resolvida(): void
    {
        $context = new CompanyContext;
        $company = new Empresa;
        $company->id = 12;

        $context->set($company);

        $this->assertTrue($context->resolved());
        $this->assertSame(12, $context->id());
        $this->assertSame($company, $context->company());
    }

    public function test_falha_quando_a_empresa_ainda_nao_foi_resolvida(): void
    {
        $this->expectException(LogicException::class);

        (new CompanyContext)->id();
    }

    public function test_nao_permite_trocar_a_empresa_durante_a_requisicao(): void
    {
        $context = new CompanyContext;
        $firstCompany = new Empresa;
        $firstCompany->id = 1;
        $secondCompany = new Empresa;
        $secondCompany->id = 2;

        $context->set($firstCompany);

        $this->expectException(LogicException::class);

        $context->set($secondCompany);
    }
}
