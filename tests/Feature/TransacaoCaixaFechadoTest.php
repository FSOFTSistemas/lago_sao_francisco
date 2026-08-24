<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\User;
use App\Services\CaixaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransacaoCaixaFechadoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('forma_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('data_abertura');
            $table->string('status');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
        });

        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->boolean('status');
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->string('categoria');
            $table->date('data_pagamento');
            $table->date('data_vencimento')->nullable();
            $table->string('tipo');
            $table->decimal('valor', 10, 2);
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('reserva_id');
            $table->string('comprovante_path')->nullable();
            $table->timestamps();
        });

        Schema::create('logs_reserva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('tipo');
            $table->text('descricao');
            $table->json('dados_antigos')->nullable();
            $table->json('dados_novos')->nullable();
            $table->timestamps();
        });

        Schema::create('movimentos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('plano_de_contas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('tipo');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        DB::table('forma_pagamentos')->insert(['id' => 1, 'descricao' => 'Dinheiro']);
        DB::table('reservas')->insert(['id' => 1]);
    }

    public function test_nao_registra_pagamento_quando_nao_existe_caixa_aberto(): void
    {
        $this->postPagamento()
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'O caixa deve estar aberto para registrar um pagamento de reserva.',
            ]);

        $this->assertDatabaseCount('transacoes', 0);
    }

    public function test_nao_registra_pagamento_quando_o_caixa_esta_fechado(): void
    {
        DB::table('caixas')->insert([
            'data_abertura' => now(),
            'status' => 'fechado',
            'empresa_id' => 10,
            'usuario_id' => 20,
        ]);

        $this->postPagamento()
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('transacoes', 0);
    }

    public function test_desfaz_o_pagamento_quando_o_movimento_financeiro_nao_esta_configurado(): void
    {
        DB::table('caixas')->insert([
            'data_abertura' => now(),
            'status' => 'aberto',
            'empresa_id' => 10,
            'usuario_id' => 20,
        ]);

        $this->postPagamento()
            ->assertServerError()
            ->assertJson([
                'success' => false,
                'message' => "Erro ao criar transação: Nenhum movimento financeiro foi configurado para a forma de pagamento 'Dinheiro'.",
            ]);

        $this->assertDatabaseCount('transacoes', 0);
        $this->assertDatabaseCount('logs_reserva', 0);
    }

    public function test_desfaz_o_pagamento_quando_o_servico_falha_ao_criar_o_movimento(): void
    {
        DB::table('caixas')->insert([
            'data_abertura' => now(),
            'status' => 'aberto',
            'empresa_id' => 10,
            'usuario_id' => 20,
        ]);
        DB::table('movimentos')->insert(['descricao' => 'venda-dinheiro']);
        DB::table('plano_de_contas')->insert([
            'descricao' => 'Hospedagem',
            'tipo' => 'receita',
            'empresa_id' => 10,
        ]);

        $caixaService = $this->mock(CaixaService::class);
        $caixaService->shouldReceive('inserirMovimentacao')
            ->once()
            ->andThrow(new \RuntimeException('Falha simulada no fluxo de caixa.'));

        $this->postPagamento()
            ->assertServerError()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('transacoes', 0);
        $this->assertDatabaseCount('logs_reserva', 0);
    }

    public function test_servico_de_caixa_rejeita_movimentacao_em_caixa_fechado(): void
    {
        $caixaId = DB::table('caixas')->insertGetId([
            'data_abertura' => now(),
            'status' => 'fechado',
            'empresa_id' => 10,
            'usuario_id' => 20,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O caixa deve estar aberto para registrar movimentações.');

        app(CaixaService::class)->inserirMovimentacao(Caixa::findOrFail($caixaId), [
            'descricao' => 'Movimento indevido',
            'valor' => 100,
            'tipo' => 'entrada',
        ]);
    }

    private function postPagamento()
    {
        $user = new User;
        $user->id = 20;
        $user->empresa_id = 10;

        return $this->actingAs($user)->postJson('/transacoes', [
            'descricao' => 'Pagamento da reserva',
            'forma_pagamento_id' => 1,
            'categoria' => 'hospedagem',
            'data_pagamento' => now()->toDateString(),
            'tipo' => 'pagamento',
            'valor' => 100,
            'reserva_id' => 1,
        ]);
    }
}
