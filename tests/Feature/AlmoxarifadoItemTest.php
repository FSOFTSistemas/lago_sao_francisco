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

class AlmoxarifadoItemTest extends TestCase
{
    private Empresa $empresa;
    private User $user;
    private AlmoxarifadoCategoria $categoria;

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
            $table->dateTime('data_movimentacao');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('fornecedor')->nullable();
            $table->string('recebido_por')->nullable();
            $table->string('retirado_por')->nullable();
            $table->string('setor')->nullable();
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
            'password' => bcrypt('12345678'),
            'empresa_id' => $this->empresa->id,
            'ativo' => true,
        ]);

        $this->categoria = AlmoxarifadoCategoria::create([
            'nome' => 'Limpeza e Higiene',
            'ativo' => true,
        ]);

        $this->actingAs($this->user);
        session(['empresa_id' => $this->empresa->id]);
    }

    public function test_listagem_de_itens_retorna_sucesso_via_json(): void
    {
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Detergente Neutro 5L',
            'unidade_medida' => 'UN',
            'estoque_atual' => 15,
            'estoque_minimo' => 5,
            'ativo' => true,
        ]);

        $response = $this->getJson(route('almoxarifado.itens.index'));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['nome' => 'Detergente Neutro 5L']);
    }

    public function test_tela_de_listagem_de_itens_renderiza_view_html_com_sucesso(): void
    {
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Sabonete 90g',
            'unidade_medida' => 'UN',
            'estoque_atual' => 50,
            'estoque_minimo' => 10,
            'ativo' => true,
        ]);

        $response = $this->get(route('almoxarifado.itens.index'));

        $response->assertOk();
        $response->assertSee('Sabonete 90g');
        $response->assertSee('Limpeza e Higiene');
        $response->assertSee('Novo Item');
    }

    public function test_cadastra_novo_item_com_sucesso(): void
    {
        $payload = [
            'categoria_id'   => $this->categoria->id,
            'nome'           => 'Desinfetante Floral 5L',
            'unidade_medida' => 'GL',
            'estoque_atual'  => 10,
            'estoque_minimo' => 3,
            'ativo'          => '1',
        ];

        $response = $this->postJson(route('almoxarifado.itens.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonFragment(['nome' => 'Desinfetante Floral 5L']);

        $this->assertDatabaseHas('almoxarifado_itens', [
            'empresa_id'     => $this->empresa->id,
            'categoria_id'   => $this->categoria->id,
            'nome'           => 'Desinfetante Floral 5L',
            'unidade_medida' => 'GL',
            'estoque_atual'  => 10,
            'estoque_minimo' => 3,
            'ativo'          => 1,
        ]);
    }

    public function test_validacao_rejeita_campos_obrigatorios_ausentes(): void
    {
        $response = $this->postJson(route('almoxarifado.itens.store'), [
            'nome' => '',
            'categoria_id' => '',
            'unidade_medida' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nome', 'categoria_id', 'unidade_medida']);
    }

    public function test_validacao_rejeita_nome_duplicado_na_mesma_empresa(): void
    {
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Sabonete Líquido 5L',
            'unidade_medida' => 'GL',
            'estoque_atual' => 5,
            'estoque_minimo' => 2,
            'ativo' => true,
        ]);

        $response = $this->postJson(route('almoxarifado.itens.store'), [
            'categoria_id' => $this->categoria->id,
            'nome' => 'Sabonete Líquido 5L',
            'unidade_medida' => 'GL',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nome']);
    }

    public function test_permite_mesmo_nome_de_item_em_empresas_diferentes(): void
    {
        $outraEmpresa = Empresa::create(['nome' => 'Hotel 2', 'cnpj' => '00.000.000/0002-72']);

        AlmoxarifadoItem::create([
            'empresa_id' => $outraEmpresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Vassoura Piaçava',
            'unidade_medida' => 'UN',
            'estoque_atual' => 5,
            'estoque_minimo' => 2,
            'ativo' => true,
        ]);

        // Tenta cadastrar o mesmo nome na empresa logada
        $response = $this->postJson(route('almoxarifado.itens.store'), [
            'categoria_id' => $this->categoria->id,
            'nome' => 'Vassoura Piaçava',
            'unidade_medida' => 'UN',
            'estoque_atual' => 8,
            'estoque_minimo' => 2,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('almoxarifado_itens', [
            'empresa_id' => $this->empresa->id,
            'nome' => 'Vassoura Piaçava',
        ]);
    }

    public function test_atualiza_dados_do_item_com_sucesso(): void
    {
        $item = AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Cloro 5L',
            'unidade_medida' => 'L',
            'estoque_atual' => 20,
            'estoque_minimo' => 5,
            'ativo' => true,
        ]);

        $response = $this->putJson(route('almoxarifado.itens.update', $item->id), [
            'categoria_id' => $this->categoria->id,
            'nome' => 'Cloro Concentrado 5L',
            'unidade_medida' => 'GL',
            'estoque_minimo' => 8,
            'ativo' => '0',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('almoxarifado_itens', [
            'id' => $item->id,
            'nome' => 'Cloro Concentrado 5L',
            'unidade_medida' => 'GL',
            'estoque_minimo' => 8,
            'estoque_atual' => 20, // Saldo permanece intacto
            'ativo' => 0,
        ]);
    }

    public function test_exclui_item_sem_movimentacoes_com_sucesso(): void
    {
        $item = AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Pano de Chão Branco',
            'unidade_medida' => 'UN',
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'ativo' => true,
        ]);

        $response = $this->deleteJson(route('almoxarifado.itens.destroy', $item->id));

        $response->assertOk();
        $this->assertDatabaseMissing('almoxarifado_itens', [
            'id' => $item->id,
        ]);
    }

    public function test_impede_exclusao_de_item_com_movimentacoes_no_historico(): void
    {
        $item = AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Lâmpada LED 9W',
            'unidade_medida' => 'UN',
            'estoque_atual' => 10,
            'estoque_minimo' => 2,
            'ativo' => true,
        ]);

        AlmoxarifadoMovimentacao::create([
            'empresa_id' => $this->empresa->id,
            'item_id' => $item->id,
            'tipo' => 'entrada',
            'quantidade' => 10,
            'data_movimentacao' => now(),
            'user_id' => $this->user->id,
            'observacao' => 'Entrada inicial',
        ]);

        $response = $this->deleteJson(route('almoxarifado.itens.destroy', $item->id));

        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);

        // Item permanece no banco
        $this->assertDatabaseHas('almoxarifado_itens', [
            'id' => $item->id,
        ]);
    }

    public function test_endpoint_de_busca_rapida_retorna_itens_filtrados(): void
    {
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Saco de Lixo 100L',
            'unidade_medida' => 'PCT',
            'estoque_atual' => 10,
            'estoque_minimo' => 2,
            'ativo' => true,
        ]);

        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Álcool 70% 1L',
            'unidade_medida' => 'UN',
            'estoque_atual' => 20,
            'estoque_minimo' => 5,
            'ativo' => true,
        ]);

        $response = $this->getJson(route('almoxarifado.itens.search', ['q' => 'Saco']));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['nome' => 'Saco de Lixo 100L']);
    }

    public function test_filtro_de_estoque_baixo_retorna_itens_no_minimo_ou_abaixo(): void
    {
        // Item 1: Estoque atual igual ao estoque mínimo (deve retornar)
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Detergente Neutro',
            'unidade_medida' => 'UN',
            'estoque_atual' => 5,
            'estoque_minimo' => 5,
            'ativo' => true,
        ]);

        // Item 2: Estoque atual abaixo do estoque mínimo (deve retornar)
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Esponja Multiuso',
            'unidade_medida' => 'UN',
            'estoque_atual' => 2,
            'estoque_minimo' => 10,
            'ativo' => true,
        ]);

        // Item 3: Estoque atual acima do estoque mínimo (NÃO deve retornar)
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Papel Higiênico',
            'unidade_medida' => 'FD',
            'estoque_atual' => 50,
            'estoque_minimo' => 10,
            'ativo' => true,
        ]);

        // Item 4: Item sem estoque mínimo cadastrado (estoque_minimo = 0) (NÃO deve retornar)
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Grampeador',
            'unidade_medida' => 'UN',
            'estoque_atual' => 0,
            'estoque_minimo' => 0,
            'ativo' => true,
        ]);

        $response = $this->getJson(route('almoxarifado.itens.index', ['estoque_baixo' => 1]));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['nome' => 'Detergente Neutro']);
        $response->assertJsonFragment(['nome' => 'Esponja Multiuso']);
        $response->assertJsonMissing(['nome' => 'Papel Higiênico']);
        $response->assertJsonMissing(['nome' => 'Grampeador']);
    }

    public function test_tela_de_listagem_exibe_contador_de_itens_em_estoque_minimo(): void
    {
        AlmoxarifadoItem::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'nome' => 'Sabonete Líquido',
            'unidade_medida' => 'UN',
            'estoque_atual' => 3,
            'estoque_minimo' => 5,
            'ativo' => true,
        ]);

        $response = $this->get(route('almoxarifado.itens.index'));

        $response->assertOk();
        $response->assertSee('Estoque Mínimo');
        $response->assertSee('Sabonete Líquido');
    }

    public function test_cadastra_item_com_diferentes_unidades_de_medida(): void
    {
        $payload = [
            'nome'           => 'Cloro Líquido 50L',
            'categoria_id'   => $this->categoria->id,
            'unidade_medida' => 'l',
            'estoque_atual'  => 50,
            'estoque_minimo' => 10,
        ];

        $response = $this->postJson(route('almoxarifado.itens.store'), $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('almoxarifado_itens', [
            'empresa_id'     => $this->empresa->id,
            'nome'           => 'Cloro Líquido 50L',
            'unidade_medida' => 'L', // Convertido em maiúsculo automaticamente
        ]);
    }
}
