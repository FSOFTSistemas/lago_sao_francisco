<?php

namespace Tests\Feature;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\AlmoxarifadoMovimentacao;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AlmoxarifadoMovimentacaoTest extends TestCase
{
    private Empresa $empresa;
    private User $user;
    private AlmoxarifadoCategoria $categoria;
    private AlmoxarifadoItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        // Cria tabelas essenciais para o teste em SQLite
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cnpj')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('almoxarifado_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('almoxarifado_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id');
            $table->string('nome');
            $table->string('unidade_medida', 20)->default('UN');
            $table->decimal('estoque_atual', 12, 2)->default(0);
            $table->decimal('estoque_minimo', 12, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('almoxarifado_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('item_id');
            $table->string('tipo');
            $table->decimal('quantidade', 12, 2);
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('saldo_posterior', 12, 2)->default(0);
            $table->dateTime('data_movimentacao');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('fornecedor')->nullable();
            $table->string('recebido_por')->nullable();
            $table->string('numero_documento')->nullable();
            $table->string('retirado_por')->nullable();
            $table->string('setor')->nullable();
            $table->string('motivo_ajuste')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        $this->empresa = Empresa::create([
            'nome' => 'Hotel Fazenda Lago',
            'cnpj' => '00.000.000/0001-91',
        ]);

        $this->user = User::create([
            'name' => 'Responsável Almoxarifado',
            'email' => 'almoxarifado@hotel.com',
            'password' => bcrypt('password'),
            'empresa_id' => $this->empresa->id,
            'ativo' => true,
        ]);

        $this->categoria = AlmoxarifadoCategoria::create([
            'nome' => 'Limpeza Geral',
            'ativo' => true,
        ]);

        $this->item = AlmoxarifadoItem::create([
            'empresa_id'     => $this->empresa->id,
            'categoria_id'   => $this->categoria->id,
            'nome'           => 'Detergente Neutro 5L',
            'unidade_medida' => 'UN',
            'estoque_atual'  => 10,
            'estoque_minimo' => 2,
            'ativo'          => true,
        ]);

        $this->actingAs($this->user);
        session(['empresa_id' => $this->empresa->id]);
    }

    public function test_listagem_de_movimentacoes_retorna_sucesso_via_json(): void
    {
        AlmoxarifadoMovimentacao::create([
            'empresa_id'        => $this->empresa->id,
            'item_id'           => $this->item->id,
            'tipo'              => 'entrada',
            'quantidade'        => 5,
            'saldo_anterior'    => 5,
            'saldo_posterior'   => 10,
            'data_movimentacao' => now(),
            'user_id'           => $this->user->id,
            'fornecedor'        => 'Distribuidora São Pedro',
        ]);

        $response = $this->getJson(route('almoxarifado.movimentacoes.index'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['tipo' => 'entrada', 'fornecedor' => 'Distribuidora São Pedro']);
    }

    public function test_registra_entrada_de_estoque_com_sucesso(): void
    {
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'entrada',
            'quantidade'        => 15,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
            'fornecedor'        => 'Fornecedor Limpeza Express',
            'recebido_por'      => 'Carlos Almoxarife',
            'numero_documento'  => 'NF-1234',
            'observacao'        => 'Recebimento de pedido mensal',
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Entrada de estoque registrada com sucesso!']);

        // Verifica que o saldo do item foi incrementado: 10 + 15 = 25
        $this->item->refresh();
        $this->assertEquals(25, (float) $this->item->estoque_atual);

        // Verifica registro na tabela de movimentações com saldos auditáveis
        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id'       => $this->empresa->id,
            'item_id'          => $this->item->id,
            'tipo'             => 'entrada',
            'quantidade'       => 15,
            'saldo_anterior'   => 10,
            'saldo_posterior'  => 25,
            'fornecedor'       => 'Fornecedor Limpeza Express',
            'recebido_por'     => 'Carlos Almoxarife',
            'numero_documento' => 'NF-1234',
        ]);
    }

    public function test_registra_saida_de_estoque_com_sucesso(): void
    {
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'saida',
            'quantidade'        => 4,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
            'retirado_por'      => 'Maria Camareira',
            'setor'             => 'Governança',
            'observacao'        => 'Uso nos chalés 01 a 05',
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Saída de estoque registrada com sucesso!']);

        // Verifica que o saldo do item foi decrementado: 10 - 4 = 6
        $this->item->refresh();
        $this->assertEquals(6, (float) $this->item->estoque_atual);

        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id'      => $this->empresa->id,
            'item_id'         => $this->item->id,
            'tipo'            => 'saida',
            'quantidade'      => 4,
            'saldo_anterior'  => 10,
            'saldo_posterior' => 6,
            'retirado_por'    => 'Maria Camareira',
            'setor'           => 'Governança',
        ]);
    }

    public function test_saida_com_estoque_insuficiente_retorna_erro_de_validacao(): void
    {
        // Item possui saldo 10, tentativa de retirar 15
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'saida',
            'quantidade'        => 15,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
            'retirado_por'      => 'João',
            'setor'             => 'Manutenção',
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['quantidade']);

        // Saldo permanece inalterado
        $this->item->refresh();
        $this->assertEquals(10, (float) $this->item->estoque_atual);

        // Nenhuma movimentação criada
        $this->assertEquals(0, AlmoxarifadoMovimentacao::count());
    }

    public function test_registra_ajuste_por_novo_saldo_fisico(): void
    {
        // Saldo atual é 10. Na contagem física de inventário, apurou-se 8 (quebra de 2)
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'ajuste',
            'novo_estoque'      => 8,
            'motivo_ajuste'     => 'Quebra / Avaria',
            'observacao'        => '2 frascos quebraram na prateleira',
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => 'Ajuste de estoque concluído com sucesso!']);

        $this->item->refresh();
        $this->assertEquals(8, (float) $this->item->estoque_atual);

        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id'      => $this->empresa->id,
            'item_id'         => $this->item->id,
            'tipo'            => 'ajuste',
            'quantidade'      => 2, // Diferença apurada: |8 - 10| = 2
            'saldo_anterior'  => 10,
            'saldo_posterior' => 8,
            'motivo_ajuste'   => 'Quebra / Avaria',
        ]);
    }

    public function test_registra_ajuste_por_acrescimo_de_quantidade(): void
    {
        // Saldo atual é 10. Ajuste de acréscimo de 5 unidades (ex: sobra de contagem)
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'ajuste',
            'tipo_ajuste'       => 'acrescimo',
            'quantidade'        => 5,
            'motivo_ajuste'     => 'Sobra de Contagem',
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(201);

        $this->item->refresh();
        $this->assertEquals(15, (float) $this->item->estoque_atual);

        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id'      => $this->empresa->id,
            'item_id'         => $this->item->id,
            'tipo'            => 'ajuste',
            'quantidade'      => 5,
            'saldo_anterior'  => 10,
            'saldo_posterior' => 15,
        ]);
    }

    public function test_registra_ajuste_por_reducao_de_quantidade(): void
    {
        // Saldo atual é 10. Ajuste de redução de 3 unidades
        $payload = [
            'item_id'           => $this->item->id,
            'tipo'              => 'ajuste',
            'tipo_ajuste'       => 'reducao',
            'quantidade'        => 3,
            'motivo_ajuste'     => 'Perda / Extravio',
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(201);

        $this->item->refresh();
        $this->assertEquals(7, (float) $this->item->estoque_atual);

        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id'      => $this->empresa->id,
            'item_id'         => $this->item->id,
            'tipo'            => 'ajuste',
            'quantidade'      => 3,
            'saldo_anterior'  => 10,
            'saldo_posterior' => 7,
        ]);
    }

    public function test_estorno_de_entrada_restaura_saldo_do_estoque(): void
    {
        // Inicial: 10 -> Entrada de 10 -> Saldo: 20
        $this->postJson(route('almoxarifado.movimentacoes.store'), [
            'item_id'           => $this->item->id,
            'tipo'              => 'entrada',
            'quantidade'        => 10,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ]);

        $mov = AlmoxarifadoMovimentacao::latest()->first();
        $this->item->refresh();
        $this->assertEquals(20, (float) $this->item->estoque_atual);

        // Estorno da entrada
        $response = $this->deleteJson(route('almoxarifado.movimentacoes.destroy', $mov->id));

        $response->assertOk();
        $this->item->refresh();
        $this->assertEquals(10, (float) $this->item->estoque_atual); // Saldo voltou para 10
        $this->assertDatabaseMissing('almoxarifado_movimentacoes', ['id' => $mov->id]);
    }

    public function test_estorno_de_saida_restaura_saldo_do_estoque(): void
    {
        // Inicial: 10 -> Saída de 4 -> Saldo: 6
        $this->postJson(route('almoxarifado.movimentacoes.store'), [
            'item_id'           => $this->item->id,
            'tipo'              => 'saida',
            'quantidade'        => 4,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ]);

        $mov = AlmoxarifadoMovimentacao::latest()->first();
        $this->item->refresh();
        $this->assertEquals(6, (float) $this->item->estoque_atual);

        // Estorno da saída
        $response = $this->deleteJson(route('almoxarifado.movimentacoes.destroy', $mov->id));

        $response->assertOk();
        $this->item->refresh();
        $this->assertEquals(10, (float) $this->item->estoque_atual); // Saldo voltou para 10
        $this->assertDatabaseMissing('almoxarifado_movimentacoes', ['id' => $mov->id]);
    }

    public function test_estorno_de_ajuste_restaura_saldo_anterior(): void
    {
        // Inicial: 10 -> Ajustado para 18
        $this->postJson(route('almoxarifado.movimentacoes.store'), [
            'item_id'           => $this->item->id,
            'tipo'              => 'ajuste',
            'novo_estoque'      => 18,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ]);

        $mov = AlmoxarifadoMovimentacao::latest()->first();
        $this->item->refresh();
        $this->assertEquals(18, (float) $this->item->estoque_atual);

        // Estorno do ajuste
        $response = $this->deleteJson(route('almoxarifado.movimentacoes.destroy', $mov->id));

        $response->assertOk();
        $this->item->refresh();
        $this->assertEquals(10, (float) $this->item->estoque_atual); // Saldo restaurado para o anterior (10)
    }

    public function test_endpoint_de_saldo_retorna_informacoes_do_item(): void
    {
        $response = $this->getJson(route('almoxarifado.movimentacoes.saldo', $this->item->id));

        $response->assertOk();
        $response->assertJson([
            'id'             => $this->item->id,
            'nome'           => 'Detergente Neutro 5L',
            'unidade_medida' => 'UN',
            'estoque_atual'  => 10,
        ]);
    }

    public function test_endpoint_de_historico_retorna_movimentacoes_do_item(): void
    {
        AlmoxarifadoMovimentacao::create([
            'empresa_id'        => $this->empresa->id,
            'item_id'           => $this->item->id,
            'tipo'              => 'entrada',
            'quantidade'        => 10,
            'saldo_anterior'    => 0,
            'saldo_posterior'   => 10,
            'data_movimentacao' => now(),
            'user_id'           => $this->user->id,
        ]);

        $response = $this->getJson(route('almoxarifado.movimentacoes.historico-item', $this->item->id));

        $response->assertOk();
        $response->assertJsonStructure(['item', 'movimentacoes']);
        $response->assertJsonCount(1, 'movimentacoes');
    }

    public function test_bloqueia_movimentacao_para_item_de_outra_empresa(): void
    {
        $outraEmpresa = Empresa::create(['nome' => 'Outra Pousada']);
        $itemOutraEmpresa = AlmoxarifadoItem::create([
            'empresa_id'     => $outraEmpresa->id,
            'categoria_id'   => $this->categoria->id,
            'nome'           => 'Item Secreto',
            'unidade_medida' => 'UN',
            'estoque_atual'  => 50,
            'ativo'          => true,
        ]);

        $payload = [
            'item_id'           => $itemOutraEmpresa->id,
            'tipo'              => 'saida',
            'quantidade'        => 5,
            'data_movimentacao' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('almoxarifado.movimentacoes.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['item_id']);
    }
}
