<?php

namespace Tests\Feature;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\CategoriaProduto;
use App\Models\DfeDocumento;
use App\Models\DfeEvento;
use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use App\Models\Entrada;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Services\DfeService;
use App\Services\EntradaXmlService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DfeEntradaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        // Cria tabelas essenciais para o teste em SQLite
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('certificado_digital')->nullable();
            $table->text('senha_certificado')->nullable();
            $table->unsignedTinyInteger('ambiente_dfe')->default(1);
            $table->string('ult_nsu', 15)->default('0');
            $table->string('max_nsu', 15)->default('0');
            $table->timestamp('data_ultima_consulta_dfe')->nullable();
            $table->string('cstat_ultima_consulta_dfe', 10)->nullable();
            $table->string('motivo_ultima_consulta_dfe', 255)->nullable();
            $table->integer('numero_ultima_nota')->nullable();
            $table->string('serie')->nullable();
            $table->string('cfop_padrao')->nullable();
            $table->string('regime_tributario')->nullable();
            $table->timestamps();
        });

        Schema::create('fornecedors', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('endereco')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->unsignedBigInteger('plano_de_conta_id')->nullable();
            $table->timestamps();
        });

        Schema::create('categoria_produtos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->unsignedBigInteger('categoria_produto_id');
            $table->boolean('ativo')->default(true);
            $table->string('ean')->nullable();
            $table->decimal('preco_custo', 10, 2)->nullable();
            $table->decimal('preco_venda', 10, 2)->default(0);
            $table->string('ncm')->nullable();
            $table->string('cst')->nullable();
            $table->string('cfop_interno')->nullable();
            $table->string('cfop_externo')->nullable();
            $table->decimal('aliquota', 5, 2)->nullable();
            $table->string('csosn')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->string('comissao')->nullable();
            $table->string('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('estoques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('empresa_id');
            $table->double('estoque_atual')->default(0);
            $table->double('entradas')->default(0);
            $table->double('saidas')->default(0);
            $table->timestamps();
        });

        Schema::create('almoxarifado_categorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
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
            $table->unsignedBigInteger('user_id');
            $table->string('tipo');
            $table->decimal('quantidade', 12, 2);
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('saldo_posterior', 12, 2)->default(0);
            $table->dateTime('data_movimentacao');
            $table->string('fornecedor')->nullable();
            $table->string('recebido_por')->nullable();
            $table->string('numero_documento')->nullable();
            $table->string('retirado_por')->nullable();
            $table->string('setor')->nullable();
            $table->string('motivo_ajuste')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('dfe_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nsu', 15);
            $table->string('chave', 44);
            $table->string('numero_nota', 20)->nullable();
            $table->string('serie', 10)->nullable();
            $table->string('schema', 30);
            $table->string('tipo_documento', 10)->default('NFE');
            $table->string('cnpj_emitente', 20)->nullable();
            $table->string('nome_emitente')->nullable();
            $table->string('ie_emitente', 20)->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->dateTime('data_emissao')->nullable();
            $table->unsignedTinyInteger('tipo_nfe')->nullable();
            $table->unsignedTinyInteger('situacao_nfe')->nullable();
            $table->string('situacao_manifestacao', 30)->default('sem_manifestacao');
            $table->dateTime('data_manifestacao')->nullable();
            $table->string('protocolo_manifestacao', 30)->nullable();
            $table->text('mensagem_manifestacao')->nullable();
            $table->longText('xml')->nullable();
            $table->boolean('importado_entrada')->default(false);
            $table->unsignedBigInteger('entrada_id')->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'chave']);
        });

        Schema::create('dfe_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('dfe_documento_id')->nullable();
            $table->string('chave', 44);
            $table->string('nsu', 15)->nullable();
            $table->string('tipo_evento', 10);
            $table->string('nome_evento', 100)->nullable();
            $table->integer('sequencia_evento')->default(1);
            $table->string('protocolo', 30)->nullable();
            $table->dateTime('data_evento')->nullable();
            $table->string('cstat', 10)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->text('justificativa')->nullable();
            $table->json('detalhes')->nullable();
            $table->longText('xml')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('dfe_documento_id')->nullable();
            $table->string('chave', 44)->unique();
            $table->string('numero_nota', 20);
            $table->string('serie', 10)->nullable();
            $table->string('natureza_operacao')->nullable();
            $table->dateTime('data_emissao');
            $table->dateTime('data_entrada');
            $table->unsignedTinyInteger('tipo_operacao')->default(0);
            $table->decimal('valor_produtos', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_seguro', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_outras_despesas', 15, 2)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('valor_icms_st', 15, 2)->default(0);
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->longText('xml')->nullable();
            $table->string('status', 30)->default('confirmada');
            $table->timestamps();
        });

        Schema::create('itens_entradas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrada_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('produto_id')->nullable();
            $table->unsignedBigInteger('almoxarifado_item_id')->nullable();
            $table->string('destino', 20)->default('produto');
            $table->integer('numero_item');
            $table->string('codigo_fornecedor')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('descricao');
            $table->string('ncm', 10)->nullable();
            $table->string('cest', 10)->nullable();
            $table->string('cfop', 10)->nullable();
            $table->string('unidade', 10)->nullable();
            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 6);
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_seguro', 15, 2)->default(0);
            $table->decimal('valor_outras_despesas', 15, 2)->default(0);
            $table->string('cst_icms', 10)->nullable();
            $table->string('csosn', 10)->nullable();
            $table->decimal('base_icms', 15, 2)->default(0);
            $table->decimal('aliquota_icms', 8, 4)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('base_icms_st', 15, 2)->default(0);
            $table->decimal('aliquota_icms_st', 8, 4)->default(0);
            $table->decimal('valor_icms_st', 15, 2)->default(0);
            $table->string('cst_pis', 10)->nullable();
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->string('cst_cofins', 10)->nullable();
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->string('cst_ipi', 10)->nullable();
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->string('cClassTrib', 10)->nullable();
            $table->decimal('pIBS', 8, 4)->nullable();
            $table->decimal('pCBS', 8, 4)->nullable();
            $table->string('cst_ibs_cbs', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->string('status')->default('pendente');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('plano_de_contas_id')->nullable();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->integer('total_parcelas')->default(1);
            $table->timestamps();
        });

        Schema::create('parcelas_contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contas_a_pagar_id');
            $table->integer('numero_parcela');
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('status')->default('pendente');
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

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
        });
    }

    public function test_processa_entrada_e_atualiza_estoque_de_produto_com_sucesso(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Hotel Lago São Francisco', 'cnpj' => '40065099000124']);
        $catProduto = CategoriaProduto::create(['descricao' => 'Bebidas']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@teste.com', 'password' => '123', 'empresa_id' => $empresa->id]);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
    <NFe>
        <infNFe Id="NFe35260199999999000199550010000000011000000015" versao="4.00">
            <ide>
                <nNF>999</nNF>
                <serie>1</serie>
                <natOp>COMPRA</natOp>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
            </ide>
            <emit>
                <CNPJ>99999999000199</CNPJ>
                <xNome>FORNECEDOR DE BEBIDAS LTDA</xNome>
                <IE>999999999</IE>
            </emit>
            <det nItem="1">
                <prod>
                    <cProd>AGUA01</cProd>
                    <cEAN>7899999999999</cEAN>
                    <xProd>AGUA MINERAL 500ML</xProd>
                    <NCM>22011000</NCM>
                    <CFOP>5102</CFOP>
                    <uCom>UN</uCom>
                    <qCom>50.0000</qCom>
                    <vUnCom>2.000000</vUnCom>
                    <vProd>100.00</vProd>
                </prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><vBC>100.00</vBC><pICMS>18.00</pICMS><vICMS>18.00</vICMS></ICMS00></ICMS></imposto>
            </det>
            <total><ICMSTot><vProd>100.00</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vOutro>0.00</vOutro><vNF>100.00</vNF></ICMSTot></total>
            <cobr><dup><nDup>001</nDup><dVenc>2026-10-22</dVenc><vDup>100.00</vDup></dup></cobr>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><chNFe>35260199999999000199550010000000011000000015</chNFe><nProt>135260000099999</nProt><cStat>100</cStat></infProt></protNFe>
</nfeProc>
XML;

        $service = new EntradaXmlService();
        $entrada = $service->processarEntrada([
            'xml'                  => $xml,
            'gerar_contas_a_pagar' => true,
            'itens'                => [
                0 => [
                    'destino'              => 'produto',
                    'categoria_produto_id' => $catProduto->id,
                    'margem_lucro'         => 50,
                ],
            ],
        ], $empresa->id, $user->id);

        $this->assertInstanceOf(Entrada::class, $entrada);
        $this->assertSame('999', $entrada->numero_nota);
        $this->assertSame(100.0, (float) $entrada->valor_total);

        // Verifica fornecedor criado
        $fornecedor = Fornecedor::where('cnpj', '99999999000199')->first();
        $this->assertNotNull($fornecedor);
        $this->assertSame('FORNECEDOR DE BEBIDAS LTDA', $fornecedor->razao_social);

        // Verifica produto e estoque
        $produto = Produto::where('empresa_id', $empresa->id)->where('descricao', 'AGUA MINERAL 500ML')->first();
        $this->assertNotNull($produto);
        $this->assertSame(2.0, (float) $produto->preco_custo);

        $this->assertDatabaseHas('estoques', [
            'produto_id'    => $produto->id,
            'empresa_id'    => $empresa->id,
            'estoque_atual' => 50,
        ]);

        // Verifica Contas a Pagar gerado
        $this->assertDatabaseHas('contas_a_pagar', [
            'empresa_id'    => $empresa->id,
            'fornecedor_id' => $fornecedor->id,
            'valor'         => 100.0,
            'status'        => 'pendente',
        ]);
    }

    public function test_processa_entrada_e_alimenta_almoxarifado_com_sucesso(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Hotel Lago São Francisco', 'cnpj' => '40065099000124']);
        $catAlmox = AlmoxarifadoCategoria::create(['nome' => 'Limpeza e Piscina']);
        $user = User::create(['name' => 'Admin', 'email' => 'admin@teste.com', 'password' => '123', 'empresa_id' => $empresa->id]);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
    <NFe>
        <infNFe Id="NFe35260188888888000188550010000000021000000025" versao="4.00">
            <ide>
                <nNF>888</nNF>
                <serie>1</serie>
                <natOp>COMPRA</natOp>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
            </ide>
            <emit>
                <CNPJ>88888888000188</CNPJ>
                <xNome>DISTRIBUIDORA DE QUIMICOS LTDA</xNome>
            </emit>
            <det nItem="1">
                <prod>
                    <cProd>CLORO10</cProd>
                    <xProd>CLORO LIQUIDO PARA PISCINA 50L</xProd>
                    <NCM>28289011</NCM>
                    <CFOP>5102</CFOP>
                    <uCom>GL</uCom>
                    <qCom>5.0000</qCom>
                    <vUnCom>80.000000</vUnCom>
                    <vProd>400.00</vProd>
                </prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><vBC>400.00</vBC><pICMS>18.00</pICMS><vICMS>72.00</vICMS></ICMS00></ICMS></imposto>
            </det>
            <total><ICMSTot><vProd>400.00</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vOutro>0.00</vOutro><vNF>400.00</vNF></ICMSTot></total>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><chNFe>35260188888888000188550010000000021000000025</chNFe><nProt>135260000088888</nProt><cStat>100</cStat></infProt></protNFe>
</nfeProc>
XML;

        $service = new EntradaXmlService();
        $entrada = $service->processarEntrada([
            'xml'   => $xml,
            'itens' => [
                0 => [
                    'destino'                   => 'almoxarifado',
                    'almoxarifado_categoria_id' => $catAlmox->id,
                ],
            ],
        ], $empresa->id, $user->id);

        $this->assertInstanceOf(Entrada::class, $entrada);
        $this->assertSame('888', $entrada->numero_nota);

        // Verifica se o item foi criado no almoxarifado
        $itemAlmox = AlmoxarifadoItem::where('empresa_id', $empresa->id)->where('nome', 'CLORO LIQUIDO PARA PISCINA 50L')->first();
        $this->assertNotNull($itemAlmox);
        $this->assertSame(5.0, (float) $itemAlmox->estoque_atual);

        // Verifica movimentação de entrada registrada no histórico
        $this->assertDatabaseHas('almoxarifado_movimentacoes', [
            'empresa_id' => $empresa->id,
            'item_id'    => $itemAlmox->id,
            'tipo'       => 'entrada',
            'quantidade' => 5.0,
        ]);
    }

    public function test_processar_entrada_dispara_confirmacao_automatica_sefaz(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Teste', 'cnpj' => '12345678000199']);
        EmpresaPreferencia::create([
            'empresa_id'   => $empresa->id,
            'ambiente_dfe' => 2,
        ]);
        $user = User::create(['name' => 'Tester', 'email' => 'tester_confirma@teste.com', 'password' => bcrypt('123'), 'empresa_id' => $empresa->id]);
        $categoria = CategoriaProduto::create(['descricao' => 'Geral']);

        $chave = '35260199999999000199550010000000031000000035';
        $dfeDoc = DfeDocumento::create([
            'empresa_id'            => $empresa->id,
            'nsu'                   => '100',
            'chave'                 => $chave,
            'numero_nota'           => '999',
            'serie'                 => '1',
            'schema'                => 'procNFe',
            'situacao_manifestacao' => 'ciencia',
        ]);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
    <NFe>
        <infNFe Id="NFe{$chave}" versao="4.00">
            <ide>
                <nNF>999</nNF>
                <serie>1</serie>
                <natOp>VENDA</natOp>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
            </ide>
            <emit>
                <CNPJ>99999999000199</CNPJ>
                <xNome>FORNECEDOR CONFIRMACAO LTDA</xNome>
            </emit>
            <det nItem="1">
                <prod>
                    <cProd>ITEM1</cProd>
                    <xProd>PRODUTO TESTE CONFIRMACAO</xProd>
                    <NCM>22021000</NCM>
                    <CFOP>5102</CFOP>
                    <uCom>UN</uCom>
                    <qCom>1.0000</qCom>
                    <vUnCom>10.000000</vUnCom>
                    <vProd>10.00</vProd>
                </prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><vBC>10.00</vBC><pICMS>18.00</pICMS><vICMS>1.80</vICMS></ICMS00></ICMS></imposto>
            </det>
            <total><ICMSTot><vProd>10.00</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vOutro>0.00</vOutro><vNF>10.00</vNF></ICMSTot></total>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><chNFe>{$chave}</chNFe><nProt>135260000099999</nProt><cStat>100</cStat></infProt></protNFe>
</nfeProc>
XML;

        $mockDfeService = Mockery::mock(DfeService::class);
        $mockDfeService->shouldReceive('manifestar')
            ->once()
            ->with(Mockery::type(Empresa::class), $chave, DfeService::EVENTO_CONFIRMACAO, '', $user->id)
            ->andReturn([
                'sucesso'   => true,
                'cStat'     => '135',
                'protocolo' => '135260000099999',
                'situacao'  => 'confirmada',
                'mensagem'  => 'Evento registrado',
            ]);
        $this->app->instance(DfeService::class, $mockDfeService);

        $service = new EntradaXmlService();
        $entrada = $service->processarEntrada([
            'xml'              => $xml,
            'dfe_documento_id' => $dfeDoc->id,
            'itens'            => [
                0 => [
                    'destino'              => 'produto',
                    'categoria_produto_id' => $categoria->id,
                ],
            ],
        ], $empresa->id, $user->id);

        $this->assertInstanceOf(Entrada::class, $entrada);
        $this->assertTrue((bool) DfeDocumento::find($dfeDoc->id)->importado_entrada);
    }

    public function test_danfe_dfe_rota_retorna_pdf_inline(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Teste', 'cnpj' => '12345678000199']);
        $user = User::create(['name' => 'Tester', 'email' => 'danfe_dfe@teste.com', 'password' => bcrypt('123'), 'empresa_id' => $empresa->id]);

        $chave = '35260199999999000199550010000000041000000045';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
    <NFe>
        <infNFe Id="NFe{$chave}" versao="4.00">
            <ide>
                <cUF>35</cUF>
                <cNF>00000004</cNF>
                <natOp>VENDA MERCADORIA</natOp>
                <mod>55</mod>
                <serie>1</serie>
                <nNF>4</nNF>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
                <tpNF>1</tpNF>
                <idDest>1</idDest>
                <cMunFG>3550308</cMunFG>
                <tpImp>1</tpImp>
                <tpEmis>1</tpEmis>
                <cDV>5</cDV>
                <tpAmb>2</tpAmb>
                <finNFe>1</finNFe>
                <indFinal>0</indFinal>
                <indPres>1</indPres>
                <procEmi>0</procEmi>
                <verProc>1.0</verProc>
            </ide>
            <emit>
                <CNPJ>99999999000199</CNPJ>
                <xNome>FORNECEDOR DANFE LTDA</xNome>
                <enderEmit>
                    <xLgr>RUA DAS FLORES</xLgr>
                    <nro>100</nro>
                    <xBairro>CENTRO</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>SAO PAULO</xMun>
                    <UF>SP</UF>
                    <CEP>01001000</CEP>
                </enderEmit>
                <IE>123456789012</IE>
                <CRT>3</CRT>
            </emit>
            <dest>
                <CNPJ>12345678000199</CNPJ>
                <xNome>HOTEL LAGO SAO FRANCISCO</xNome>
                <enderDest>
                    <xLgr>RODOVIA SP</xLgr>
                    <nro>KM 10</nro>
                    <xBairro>ZONA RURAL</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>SAO PAULO</xMun>
                    <UF>SP</UF>
                    <CEP>14000000</CEP>
                </enderDest>
                <indIEDest>1</indIEDest>
                <IE>987654321098</IE>
            </dest>
            <det nItem="1">
                <prod>
                    <cProd>ITEM1</cProd>
                    <cEAN>SEM GTIN</cEAN>
                    <xProd>PRODUTO TESTE DANFE</xProd>
                    <NCM>22021000</NCM>
                    <CFOP>5102</CFOP>
                    <uCom>UN</uCom>
                    <qCom>1.0000</qCom>
                    <vUnCom>10.000000</vUnCom>
                    <vProd>10.00</vProd>
                    <cEANTrib>SEM GTIN</cEANTrib>
                    <uTrib>UN</uTrib>
                    <qTrib>1.0000</qTrib>
                    <vUnTrib>10.000000</vUnTrib>
                    <indTot>1</indTot>
                </prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>10.00</vBC><pICMS>18.00</pICMS><vICMS>1.80</vICMS></ICMS00></ICMS><PIS><PISAliq><CST>01</CST><vBC>10.00</vBC><pPIS>1.65</pPIS><vPIS>0.17</vPIS></PISAliq></PIS><COFINS><COFINSAliq><CST>01</CST><vBC>10.00</vBC><pCOFINS>7.60</pCOFINS><vCOFINS>0.76</vCOFINS></COFINSAliq></COFINS></imposto>
            </det>
            <total><ICMSTot><vBC>10.00</vBC><vICMS>1.80</vICMS><vICMSDeson>0.00</vICMSDeson><vFCP>0.00</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>0.00</vFCPST><vFCPSTRet>0.00</vFCPSTRet><vProd>10.00</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>0.00</vIPIDevol><vPIS>0.17</vPIS><vCOFINS>0.76</vCOFINS><vOutro>0.00</vOutro><vNF>10.00</vNF></ICMSTot></total>
            <transp><modFrete>9</modFrete></transp>
            <pag><detPag><tPag>01</tPag><vPag>10.00</vPag></detPag></pag>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><tpAmb>2</tpAmb><verAplic>1.0</verAplic><chNFe>{$chave}</chNFe><dhRecbto>2026-09-22T10:00:00-03:00</dhRecbto><nProt>135260000099998</nProt><digVal>abcdef123456789=</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>
</nfeProc>
XML;

        $doc = DfeDocumento::create([
            'empresa_id'     => $empresa->id,
            'nsu'            => '101',
            'chave'          => $chave,
            'numero_nota'    => '4',
            'serie'          => '1',
            'schema'         => 'procNFe',
            'tipo_documento' => 'NFE',
            'cnpj_emitente'  => '99999999000199',
            'nome_emitente'  => 'FORNECEDOR DANFE LTDA',
            'valor_total'    => 10.00,
            'situacao_nfe'   => 1,
            'xml'            => $xml,
        ]);

        $response = $this->actingAs($user)->get(route('dfe.danfe', $doc->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_danfe_entrada_rota_retorna_pdf_inline(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Teste', 'cnpj' => '12345678000199']);
        $user = User::create(['name' => 'Tester', 'email' => 'danfe_entrada@teste.com', 'password' => bcrypt('123'), 'empresa_id' => $empresa->id]);
        $fornecedor = Fornecedor::create(['razao_social' => 'Fornecedor Teste', 'cnpj' => '99999999000199']);

        $chave = '35260199999999000199550010000000051000000055';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc versao="4.00" xmlns="http://www.portalfiscal.inf.br/nfe">
    <NFe>
        <infNFe Id="NFe{$chave}" versao="4.00">
            <ide>
                <cUF>35</cUF>
                <cNF>00000005</cNF>
                <natOp>VENDA MERCADORIA</natOp>
                <mod>55</mod>
                <serie>1</serie>
                <nNF>5</nNF>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
                <tpNF>1</tpNF>
                <idDest>1</idDest>
                <cMunFG>3550308</cMunFG>
                <tpImp>1</tpImp>
                <tpEmis>1</tpEmis>
                <cDV>5</cDV>
                <tpAmb>2</tpAmb>
                <finNFe>1</finNFe>
                <indFinal>0</indFinal>
                <indPres>1</indPres>
                <procEmi>0</procEmi>
                <verProc>1.0</verProc>
            </ide>
            <emit>
                <CNPJ>99999999000199</CNPJ>
                <xNome>FORNECEDOR ENTRADA LTDA</xNome>
                <enderEmit>
                    <xLgr>RUA DAS FLORES</xLgr>
                    <nro>100</nro>
                    <xBairro>CENTRO</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>SAO PAULO</xMun>
                    <UF>SP</UF>
                    <CEP>01001000</CEP>
                </enderEmit>
                <IE>123456789012</IE>
                <CRT>3</CRT>
            </emit>
            <dest>
                <CNPJ>12345678000199</CNPJ>
                <xNome>HOTEL LAGO SAO FRANCISCO</xNome>
                <enderDest>
                    <xLgr>RODOVIA SP</xLgr>
                    <nro>KM 10</nro>
                    <xBairro>ZONA RURAL</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>SAO PAULO</xMun>
                    <UF>SP</UF>
                    <CEP>14000000</CEP>
                </enderDest>
                <indIEDest>1</indIEDest>
                <IE>987654321098</IE>
            </dest>
            <det nItem="1">
                <prod>
                    <cProd>ITEM1</cProd>
                    <cEAN>SEM GTIN</cEAN>
                    <xProd>PRODUTO TESTE ENTRADA DANFE</xProd>
                    <NCM>22021000</NCM>
                    <CFOP>5102</CFOP>
                    <uCom>UN</uCom>
                    <qCom>1.0000</qCom>
                    <vUnCom>20.000000</vUnCom>
                    <vProd>20.00</vProd>
                    <cEANTrib>SEM GTIN</cEANTrib>
                    <uTrib>UN</uTrib>
                    <qTrib>1.0000</qTrib>
                    <vUnTrib>20.000000</vUnTrib>
                    <indTot>1</indTot>
                </prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><modBC>3</modBC><vBC>20.00</vBC><pICMS>18.00</pICMS><vICMS>3.60</vICMS></ICMS00></ICMS><PIS><PISAliq><CST>01</CST><vBC>20.00</vBC><pPIS>1.65</pPIS><vPIS>0.33</vPIS></PISAliq></PIS><COFINS><COFINSAliq><CST>01</CST><vBC>20.00</vBC><pCOFINS>7.60</pCOFINS><vCOFINS>1.52</vCOFINS></COFINSAliq></COFINS></imposto>
            </det>
            <total><ICMSTot><vBC>20.00</vBC><vICMS>3.60</vICMS><vICMSDeson>0.00</vICMSDeson><vFCP>0.00</vFCP><vBCST>0.00</vBCST><vST>0.00</vST><vFCPST>0.00</vFCPST><vFCPSTRet>0.00</vFCPSTRet><vProd>20.00</vProd><vFrete>0.00</vFrete><vSeg>0.00</vSeg><vDesc>0.00</vDesc><vII>0.00</vII><vIPI>0.00</vIPI><vIPIDevol>0.00</vIPIDevol><vPIS>0.33</vPIS><vCOFINS>1.52</vCOFINS><vOutro>0.00</vOutro><vNF>20.00</vNF></ICMSTot></total>
            <transp><modFrete>9</modFrete></transp>
            <pag><detPag><tPag>01</tPag><vPag>20.00</vPag></detPag></pag>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><tpAmb>2</tpAmb><verAplic>1.0</verAplic><chNFe>{$chave}</chNFe><dhRecbto>2026-09-22T10:00:00-03:00</dhRecbto><nProt>135260000099997</nProt><digVal>abcdef123456788=</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>
</nfeProc>
XML;

        $entrada = Entrada::create([
            'empresa_id'        => $empresa->id,
            'fornecedor_id'     => $fornecedor->id,
            'usuario_id'        => $user->id,
            'chave'             => $chave,
            'numero_nota'       => '5',
            'serie'             => '1',
            'natureza_operacao' => 'VENDA',
            'data_emissao'      => now(),
            'data_entrada'      => now(),
            'valor_produtos'    => 20.00,
            'valor_total'       => 20.00,
            'xml'               => $xml,
            'status'            => 'confirmada',
        ]);

        $response = $this->actingAs($user)->get(route('entradas.danfe', $entrada->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_recebimento_de_resumo_e_depois_completo_nao_duplica_documento(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Teste Unicidade', 'cnpj' => '12345678000199']);
        $service = new DfeService();

        $chave = '35260199999999000199550010000000061000000065';

        // 1. Chega o resumo da nota (resNFe)
        $xmlResumo = <<<XML
<resNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.01">
    <chNFe>{$chave}</chNFe>
    <CNPJ>99999999000199</CNPJ>
    <xNome>FORNECEDOR UNICIDADE LTDA</xNome>
    <vNF>150.00</vNF>
    <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
    <tpNF>1</tpNF>
    <cSitNFe>1</cSitNFe>
</resNFe>
XML;

        $doc1 = $service->processarDocumentoXml($empresa, '1001', 'resNFe', $xmlResumo);
        $this->assertNotNull($doc1);
        $this->assertSame('resNFe', $doc1->schema);
        $this->assertFalse($doc1->temXmlCompleto());

        // Deve existir exatamente 1 registro no banco
        $this->assertSame(1, DfeDocumento::where('empresa_id', $empresa->id)->where('chave', $chave)->count());

        // 2. Posteriormente, chega a nota completa (procNFe)
        $xmlCompleto = <<<XML
<nfeProc xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
    <NFe>
        <infNFe Id="NFe{$chave}" versao="4.00">
            <ide><nNF>6</nNF><serie>1</serie><dhEmi>2026-09-22T10:00:00-03:00</dhEmi><tpNF>1</tpNF></ide>
            <emit><CNPJ>99999999000199</CNPJ><xNome>FORNECEDOR UNICIDADE LTDA</xNome></emit>
            <det nItem="1">
                <prod><cProd>P1</cProd><xProd>PROD 1</xProd><uCom>UN</uCom><qCom>1</qCom><vUnCom>150</vUnCom><vProd>150.00</vProd></prod>
                <imposto><ICMS><ICMS00><orig>0</orig><CST>00</CST><vBC>150</vBC><pICMS>18</pICMS><vICMS>27</vICMS></ICMS00></ICMS></imposto>
            </det>
            <total><ICMSTot><vProd>150.00</vProd><vNF>150.00</vNF></ICMSTot></total>
        </infNFe>
    </NFe>
    <protNFe versao="4.00"><infProt><chNFe>{$chave}</chNFe><nProt>135260000099990</nProt><cStat>100</cStat></infProt></protNFe>
</nfeProc>
XML;

        $doc2 = $service->processarDocumentoXml($empresa, '1005', 'procNFe', $xmlCompleto);
        $this->assertNotNull($doc2);
        $this->assertSame('procNFe', $doc2->schema);
        $this->assertTrue($doc2->temXmlCompleto());

        // AINDA deve existir exatamente 1 registro no banco (mesmo ID atualizado, sem duplicar)
        $this->assertSame(1, DfeDocumento::where('empresa_id', $empresa->id)->where('chave', $chave)->count());
        $this->assertSame($doc1->id, $doc2->id);
    }

    public function test_filtro_status_xml_retorna_apenas_notas_completas_por_padrao(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Filtro', 'cnpj' => '12345678000199']);
        $user = User::create(['name' => 'Tester', 'email' => 'filtro_dfe@teste.com', 'password' => bcrypt('123'), 'empresa_id' => $empresa->id]);

        $chaveCompleta = '35260199999999000199550010000001001000000100';
        $chaveResumo   = '35260199999999000199550010000001011000000101';

        DfeDocumento::create([
            'empresa_id'   => $empresa->id,
            'nsu'          => '2001',
            'chave'        => $chaveCompleta,
            'schema'       => 'procNFe',
            'numero_nota'  => '99100',
            'serie'        => '1',
            'nome_emitente'=> 'EMITENTE COMPLETO',
            'data_emissao' => now(),
            'valor_total'  => 500.00,
            'situacao_nfe' => 1,
            'xml'          => '<nfeProc><NFe></NFe></nfeProc>',
        ]);

        DfeDocumento::create([
            'empresa_id'   => $empresa->id,
            'nsu'          => '2002',
            'chave'        => $chaveResumo,
            'schema'       => 'resNFe',
            'numero_nota'  => '99101',
            'serie'        => '1',
            'nome_emitente'=> 'EMITENTE RESUMO',
            'data_emissao' => now(),
            'valor_total'  => 250.00,
            'situacao_nfe' => 1,
            'xml'          => null,
        ]);

        // 1. Por padrão, rota exibe apenas notas com XML Completo
        $responsePadrao = $this->actingAs($user)->getJson(route('dfe.index'));
        $responsePadrao->assertStatus(200);
        $chavesPadrao = collect($responsePadrao->json('data'))->pluck('chave')->all();
        $this->assertContains($chaveCompleta, $chavesPadrao);
        $this->assertNotContains($chaveResumo, $chavesPadrao);

        // 2. Filtro com_xml=0 exibe apenas resumos
        $responseResumo = $this->actingAs($user)->getJson(route('dfe.index', ['com_xml' => '0']));
        $responseResumo->assertStatus(200);
        $chavesResumo = collect($responseResumo->json('data'))->pluck('chave')->all();
        $this->assertContains($chaveResumo, $chavesResumo);
        $this->assertNotContains($chaveCompleta, $chavesResumo);

        // 3. Filtro com_xml=todos exibe ambas as notas
        $responseTodos = $this->actingAs($user)->getJson(route('dfe.index', ['com_xml' => 'todos']));
        $responseTodos->assertStatus(200);
        $chavesTodos = collect($responseTodos->json('data'))->pluck('chave')->all();
        $this->assertContains($chaveCompleta, $chavesTodos);
        $this->assertContains($chaveResumo, $chavesTodos);
    }

    public function test_processamento_de_evento_sefaz_grava_em_dfe_eventos_e_cancela_nota(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Evento', 'cnpj' => '12345678000199']);
        $service = new DfeService();
        $chave = '35260199999999000199550010000000071000000075';

        // Cria a nota previamente
        $doc = DfeDocumento::create([
            'empresa_id'   => $empresa->id,
            'nsu'          => '3001',
            'chave'        => $chave,
            'schema'       => 'procNFe',
            'numero_nota'  => '7',
            'serie'        => '1',
            'situacao_nfe' => 1, // Autorizada
        ]);

        // Chega evento de Cancelamento (110111)
        $xmlEvento = <<<XML
<procEventoNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.00">
    <evento versao="1.00">
        <infEvento Id="ID110111{$chave}01">
            <cOrgao>35</cOrgao>
            <tpAmb>2</tpAmb>
            <CNPJ>99999999000199</CNPJ>
            <chNFe>{$chave}</chNFe>
            <dhEvento>2026-09-23T11:00:00-03:00</dhEvento>
            <tpEvento>110111</tpEvento>
            <nSeqEvento>1</nSeqEvento>
            <verEvento>1.00</verEvento>
            <detEvento versao="1.00">
                <descEvento>Cancelamento</descEvento>
                <nProt>135260000099999</nProt>
                <xJust>Cancelamento solicitado pelo emitente</xJust>
            </detEvento>
        </infEvento>
    </evento>
    <retEvento versao="1.00">
        <infEvento>
            <tpAmb>2</tpAmb>
            <cOrgao>35</cOrgao>
            <cStat>135</cStat>
            <xMotivo>Evento registrado e vinculado a NF-e</xMotivo>
            <chNFe>{$chave}</chNFe>
            <tpEvento>110111</tpEvento>
            <xEvento>Cancelamento homologado</xEvento>
            <nSeqEvento>1</nSeqEvento>
            <dhRegEvento>2026-09-23T11:00:05-03:00</dhRegEvento>
            <nProt>135260000088888</nProt>
        </infEvento>
    </retEvento>
</procEventoNFe>
XML;

        $service->processarDocumentoXml($empresa, '3002', 'procEventoNFe', $xmlEvento);

        // Verifica se a nota teve seu status alterado para cancelada (situacao_nfe = 2)
        $doc->refresh();
        $this->assertSame(2, $doc->situacao_nfe);

        // Verifica se o evento foi gravado na tabela dfe_eventos
        $evento = DfeEvento::where('empresa_id', $empresa->id)->where('chave', $chave)->first();
        $this->assertNotNull($evento);
        $this->assertSame('110111', $evento->tipo_evento);
        $this->assertSame($doc->id, $evento->dfe_documento_id);
        $this->assertSame('Cancelamento homologado', $evento->nome_evento);
        $this->assertSame('135260000088888', $evento->protocolo);
    }

    public function test_endpoint_eventos_retorna_historico_json(): void
    {
        $empresa = Empresa::create(['razao_social' => 'Empresa Endpoint', 'cnpj' => '12345678000199']);
        $user = User::create(['name' => 'Tester Eventos', 'email' => 'eventos@teste.com', 'password' => bcrypt('123'), 'empresa_id' => $empresa->id]);
        $chave = '35260199999999000199550010000000081000000085';

        $doc = DfeDocumento::create([
            'empresa_id'   => $empresa->id,
            'nsu'          => '4001',
            'chave'        => $chave,
            'schema'       => 'procNFe',
            'numero_nota'  => '8',
            'serie'        => '1',
            'situacao_nfe' => 1,
            'valor_total'  => 1000.00,
        ]);

        DfeEvento::create([
            'empresa_id'       => $empresa->id,
            'dfe_documento_id' => $doc->id,
            'chave'            => $chave,
            'tipo_evento'      => '210210',
            'nome_evento'      => 'Ciência da Emissão',
            'sequencia_evento' => 1,
            'protocolo'        => '135260000011111',
            'data_evento'      => now(),
            'cstat'            => '135',
            'motivo'           => 'Evento registrado',
            'user_id'          => $user->id,
        ]);

        DfeEvento::create([
            'empresa_id'       => $empresa->id,
            'dfe_documento_id' => $doc->id,
            'chave'            => $chave,
            'tipo_evento'      => '210200',
            'nome_evento'      => 'Confirmação da Operação',
            'sequencia_evento' => 1,
            'protocolo'        => '135260000022222',
            'data_evento'      => now(),
            'cstat'            => '135',
            'motivo'           => 'Evento registrado',
            'user_id'          => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson(route('dfe.eventos', $chave));
        $response->assertStatus(200);
        $response->assertJson([
            'sucesso' => true,
            'chave'   => $chave,
            'numero'  => '8',
        ]);

        $eventosRetornados = $response->json('eventos');
        $this->assertCount(2, $eventosRetornados);
        $this->assertSame('Confirmação da Operação', $eventosRetornados[0]['nome_evento']);
        $this->assertSame('Tester Eventos', $eventosRetornados[0]['usuario']);
    }
}

