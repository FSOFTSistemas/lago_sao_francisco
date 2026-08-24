<?php

namespace Tests\Feature;

use App\Models\ContaCorrenteLancamento;
use App\Models\ContasAPagar;
use App\Models\ParcelaContasAPagar;
use App\Models\User;
use App\Services\ContaCorrenteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class ContasAPagarPagamentoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->string('status')->default('pendente');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_contas_id')->nullable();
            $table->unsignedInteger('total_parcelas')->nullable();
            $table->timestamps();
        });

        Schema::create('parcelas_contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contas_a_pagar_id');
            $table->unsignedInteger('numero_parcela');
            $table->decimal('valor', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->string('forma_pagamento')->nullable();
            $table->string('status')->default('pendente');
            $table->timestamps();
        });

        Schema::create('contas_correntes', function (Blueprint $table) {
            $table->id();
        });

        DB::table('contas_correntes')->insert(['id' => 1]);
    }

    public function test_pagamento_integral_quita_parcela_e_conta(): void
    {
        [$conta, $parcela] = $this->criarContaComParcela();
        $this->mockContaCorrenteService(100);

        $this->actingAs($this->usuario())->post(route('contasAPagar.pagar', [$conta->id, $parcela->id]), [
            'data_pagamento' => '2026-08-24',
            'valor_pago' => 100,
            'fonte_pagadora' => 'conta_corrente',
            'conta_corrente_id' => 1,
        ])->assertRedirect(route('contasAPagar.index'))
            ->assertSessionHas('success', 'Pagamento registrado com sucesso!');

        $this->assertDatabaseHas('parcelas_contas_a_pagar', [
            'id' => $parcela->id,
            'valor_pago' => 100,
            'forma_pagamento' => 'conta_corrente',
            'status' => 'pago',
        ]);
        $this->assertDatabaseHas('contas_a_pagar', [
            'id' => $conta->id,
            'valor_pago' => 100,
            'status' => 'pago',
        ]);
    }

    public function test_pagamento_parcial_mantem_parcela_pendente(): void
    {
        [$conta, $parcela] = $this->criarContaComParcela();
        $this->mockContaCorrenteService(40);

        $this->actingAs($this->usuario())->post(route('contasAPagar.pagar', [$conta->id, $parcela->id]), [
            'data_pagamento' => '2026-08-24',
            'valor_pago' => 40,
            'fonte_pagadora' => 'conta_corrente',
            'conta_corrente_id' => 1,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('parcelas_contas_a_pagar', [
            'id' => $parcela->id,
            'valor_pago' => 40,
            'forma_pagamento' => 'conta_corrente',
            'status' => 'pendente',
        ]);
        $this->assertDatabaseHas('contas_a_pagar', [
            'id' => $conta->id,
            'valor_pago' => 40,
            'status' => 'pendente',
        ]);
    }

    public function test_falha_na_saida_financeira_nao_altera_parcela(): void
    {
        [$conta, $parcela] = $this->criarContaComParcela();

        $service = $this->mock(ContaCorrenteService::class);
        $service->shouldReceive('registrarLancamento')
            ->once()
            ->andThrow(new InvalidArgumentException('Saldo insuficiente na conta corrente.'));

        $this->actingAs($this->usuario())->post(route('contasAPagar.pagar', [$conta->id, $parcela->id]), [
            'data_pagamento' => '2026-08-24',
            'valor_pago' => 100,
            'fonte_pagadora' => 'conta_corrente',
            'conta_corrente_id' => 1,
        ])->assertSessionHas('error', 'Saldo insuficiente na conta corrente.');

        $this->assertDatabaseHas('parcelas_contas_a_pagar', [
            'id' => $parcela->id,
            'valor_pago' => 0,
            'forma_pagamento' => null,
            'status' => 'pendente',
        ]);
        $this->assertDatabaseHas('contas_a_pagar', [
            'id' => $conta->id,
            'valor_pago' => 0,
            'status' => 'pendente',
        ]);
    }

    private function criarContaComParcela(): array
    {
        $conta = ContasAPagar::create([
            'descricao' => 'Conta de teste',
            'valor' => 100,
            'valor_pago' => 0,
            'data_vencimento' => '2026-08-24',
            'status' => 'pendente',
            'empresa_id' => 10,
            'total_parcelas' => 1,
        ]);

        $parcela = ParcelaContasAPagar::create([
            'contas_a_pagar_id' => $conta->id,
            'numero_parcela' => 1,
            'valor' => 100,
            'valor_pago' => 0,
            'data_vencimento' => '2026-08-24',
            'status' => 'pendente',
        ]);

        return [$conta, $parcela];
    }

    private function usuario(): User
    {
        $usuario = new User;
        $usuario->id = 20;
        $usuario->empresa_id = 10;
        $usuario->setRelation('roles', collect());

        return $usuario;
    }

    private function mockContaCorrenteService(float $valor): void
    {
        $service = $this->mock(ContaCorrenteService::class);
        $service->shouldReceive('registrarLancamento')
            ->once()
            ->withArgs(fn (array $dados, int $empresaId) => $dados['valor'] == $valor
                && $dados['tipo'] === 'saida'
                && $empresaId === 10)
            ->andReturn(new ContaCorrenteLancamento);
    }
}
