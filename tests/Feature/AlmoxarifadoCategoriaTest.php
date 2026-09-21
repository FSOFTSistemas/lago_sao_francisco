<?php

namespace Tests\Feature;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AlmoxarifadoCategoriaTest extends TestCase
{
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

        // Notificações do Laravel necessárias para a navbar do AdminLTE
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Tabelas de permissões do Spatie necessárias para renderização do AdminLTE
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

        $empresa = Empresa::create([
            'nome' => 'Empresa Teste',
            'cnpj' => '00.000.000/0001-91',
        ]);

        $user = User::create([
            'name' => 'Admin Almoxarifado',
            'email' => 'admin@teste.com',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresa->id,
            'ativo' => true,
        ]);

        $this->actingAs($user);
    }

    public function test_listagem_de_categorias_retorna_sucesso_e_exibe_registros(): void
    {
        AlmoxarifadoCategoria::create(['nome' => 'Limpeza e Higiene', 'ativo' => true]);
        AlmoxarifadoCategoria::create(['nome' => 'Manutenção Elétrica', 'ativo' => false]);

        $response = $this->get(route('almoxarifado.categorias.index'));

        $response->assertOk();
        $response->assertSee('Limpeza e Higiene');
        $response->assertSee('Manutenção Elétrica');
        $response->assertSee('Ativo');
        $response->assertSee('Inativo');
    }

    public function test_busca_de_categorias_filtra_por_nome(): void
    {
        AlmoxarifadoCategoria::create(['nome' => 'Limpeza Pesada', 'ativo' => true]);
        AlmoxarifadoCategoria::create(['nome' => 'Manutenção Predial', 'ativo' => true]);

        // Busca que encontra
        $response = $this->get(route('almoxarifado.categorias.index', ['busca' => 'Limpeza']));
        $response->assertOk();
        $response->assertSee('Limpeza Pesada');
        $response->assertDontSee('Manutenção Predial');

        // Busca que não encontra
        $responseVazia = $this->get(route('almoxarifado.categorias.index', ['busca' => 'Inexistente']));
        $responseVazia->assertOk();
        $responseVazia->assertSee('Nenhuma categoria encontrada para');
        $responseVazia->assertDontSee('Limpeza Pesada');
    }

    public function test_cadastra_nova_categoria_com_sucesso(): void
    {
        $response = $this->post(route('almoxarifado.categorias.store'), [
            'nome' => 'Rouparia e Enxoval',
            'ativo' => '1',
        ]);

        $response->assertRedirect(route('almoxarifado.categorias.index'));
        $response->assertSessionHas('success', 'Categoria cadastrada com sucesso!');

        $this->assertDatabaseHas('almoxarifado_categorias', [
            'nome' => 'Rouparia e Enxoval',
            'ativo' => 1,
        ]);
    }

    public function test_validacao_rejeita_nome_vazio_ou_duplicado(): void
    {
        AlmoxarifadoCategoria::create(['nome' => 'Escritório', 'ativo' => true]);

        // Nome vazio
        $responseVazio = $this->post(route('almoxarifado.categorias.store'), [
            'nome' => '',
            'ativo' => '1',
        ]);
        $responseVazio->assertSessionHasErrors('nome');

        // Nome duplicado
        $responseDuplicado = $this->post(route('almoxarifado.categorias.store'), [
            'nome' => 'Escritório',
            'ativo' => '1',
        ]);
        $responseDuplicado->assertSessionHasErrors('nome');
    }

    public function test_atualiza_categoria_com_sucesso(): void
    {
        $categoria = AlmoxarifadoCategoria::create(['nome' => 'Piscina', 'ativo' => true]);

        $response = $this->put(route('almoxarifado.categorias.update', $categoria->id), [
            'nome' => 'Piscina e Área Externa',
            'ativo' => '0',
        ]);

        $response->assertRedirect(route('almoxarifado.categorias.index'));
        $response->assertSessionHas('success', 'Categoria atualizada com sucesso!');

        $this->assertDatabaseHas('almoxarifado_categorias', [
            'id' => $categoria->id,
            'nome' => 'Piscina e Área Externa',
            'ativo' => 0,
        ]);
    }

    public function test_exclui_categoria_sem_itens_com_sucesso(): void
    {
        $categoria = AlmoxarifadoCategoria::create(['nome' => 'Descartáveis', 'ativo' => true]);

        $response = $this->delete(route('almoxarifado.categorias.destroy', $categoria->id));

        $response->assertRedirect(route('almoxarifado.categorias.index'));
        $response->assertSessionHas('success', 'Categoria excluída com sucesso!');

        $this->assertDatabaseMissing('almoxarifado_categorias', [
            'id' => $categoria->id,
        ]);
    }

    public function test_impede_exclusao_de_categoria_que_possui_itens_vinculados(): void
    {
        $empresa = Empresa::first();
        $categoria = AlmoxarifadoCategoria::create(['nome' => 'Manutenção', 'ativo' => true]);

        AlmoxarifadoItem::create([
            'empresa_id' => $empresa->id,
            'categoria_id' => $categoria->id,
            'nome' => 'Fita Isolante 20m',
            'unidade_medida' => 'UN',
            'estoque_atual' => 5,
            'estoque_minimo' => 2,
            'ativo' => true,
        ]);

        $response = $this->delete(route('almoxarifado.categorias.destroy', $categoria->id));

        $response->assertRedirect(route('almoxarifado.categorias.index'));
        $response->assertSessionHas('error');

        // Confirma que a categoria NÃO foi excluída
        $this->assertDatabaseHas('almoxarifado_categorias', [
            'id' => $categoria->id,
        ]);
    }
}
