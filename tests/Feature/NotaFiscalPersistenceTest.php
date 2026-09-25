<?php

namespace Tests\Feature;

use App\Http\Controllers\NotaFiscalController;
use App\Models\NotaFiscal;
use App\Models\NotaFiscalItem;
use App\Models\User;
use App\Services\NFeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Mockery;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use Tests\TestCase;

class NotaFiscalPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        // Cria tabelas necessárias para os testes em SQLite
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Admin');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->timestamps();
        });

        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('logradouro')->default('Rua Principal');
            $table->string('numero')->default('100');
            $table->string('complemento')->nullable();
            $table->string('bairro')->default('Centro');
            $table->string('cidade')->default('Garanhuns');
            $table->string('uf')->default('PE');
            $table->string('cep')->default('55290000');
            $table->string('ibge')->default('2606002');
            $table->timestamps();
        });

        Schema::create('empresa_r_t_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->string('nome')->default('Fsoft Sistemas LTDA');
            $table->string('cnpj')->default('60177690000180');
            $table->string('telefone')->default('87999999999');
            $table->string('email')->default('contato@fsoft.com.br');
            $table->timestamps();
        });

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->unsignedBigInteger('responsavel_tecnico_id')->nullable();
            $table->unsignedBigInteger('contador_id')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->integer('ambiente_dfe')->default(2);
            $table->integer('numero_ultima_nota')->default(0);
            $table->string('serie')->default('1');
            $table->string('cfop_padrao')->default('5102');
            $table->string('regime_tributario')->default('Simples Nacional');
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_razao_social');
            $table->string('cpf_cnpj')->nullable();
            $table->string('rg_ie')->nullable();
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->double('preco_venda')->default(0);
            $table->string('ncm')->default('21069090');
            $table->string('cfop_interno')->default('5102');
            $table->string('ean')->nullable();
            $table->string('csosn')->default('102');
            $table->string('cst')->default('00');
            $table->double('aliquota')->default(0);
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->timestamps();
        });

        Schema::create('cfops', function (Blueprint $table) {
            $table->id();
            $table->string('cfop');
            $table->string('natureza');
            $table->timestamps();
        });

        Schema::create('ncms', function (Blueprint $table) {
            $table->id();
            $table->string('ncm');
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('nota_fiscals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('ncm_id')->default(1);
            $table->unsignedBigInteger('cfop_id')->default(1);
            $table->unsignedBigInteger('usuario_id')->default(1);
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->date('data');
            $table->string('chave')->nullable();
            $table->string('status')->default('pendente');
            $table->string('cstat')->nullable();
            $table->string('protocolo')->nullable();
            $table->string('motivo_status')->nullable();
            $table->timestamp('data_autorizacao')->nullable();
            $table->integer('serie');
            $table->integer('numero');
            $table->string('observacoes')->default('');
            $table->string('info_complementares')->default('');
            $table->double('peso_liquido')->default(0);
            $table->double('peso_bruto')->default(0);
            $table->integer('tp_frete')->default(9);
            $table->integer('tp_transporte')->default(0);
            $table->integer('tp_nota')->default(1);
            $table->string('nfe_referenciavel')->nullable();
            $table->double('total_produtos')->default(0);
            $table->double('total_nota')->default(0);
            $table->double('total_notas')->default(0);
            $table->double('total_desconto')->default(0);
            $table->double('outras_despesas')->default(0);
            $table->double('base_ICMS')->default(0);
            $table->double('vICMS')->default(0);
            $table->double('base_ST')->default(0);
            $table->double('v_ST')->default(0);
            $table->timestamps();
        });

        Schema::create('nota_fiscal_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_fiscal_id');
            $table->unsignedBigInteger('produto_id');
            $table->integer('quantidade');
            $table->double('v_unitario');
            $table->double('desconto')->default(0);
            $table->double('subtotal');
            $table->string('cst')->default('00');
            $table->unsignedBigInteger('cfop_id')->default(1);
            $table->string('csosm')->default('102');
            $table->double('total');
            $table->double('base_ICMS')->default(0);
            $table->double('vICMS')->default(0);
            $table->double('base_ST')->default(0);
            $table->double('v_ST')->default(0);
            $table->timestamps();
        });

        // Seed básico
        DB::table('enderecos')->insert([
            'id' => 1,
            'logradouro' => 'Rua Principal',
            'numero' => '100',
            'bairro' => 'Centro',
            'cidade' => 'Garanhuns',
            'uf' => 'PE',
            'cep' => '55290000',
            'ibge' => '2606002',
        ]);

        DB::table('empresa_r_t_s')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'nome' => 'Fsoft Sistemas LTDA',
            'cnpj' => '60177690000180',
            'telefone' => '87999999999',
            'email' => 'contato@fsoft.com.br',
        ]);

        DB::table('empresas')->insert([
            'id' => 1,
            'razao_social' => 'HOTEL LAGO LTDA',
            'nome_fantasia' => 'HOTEL LAGO',
            'cnpj' => '38090491000181',
            'inscricao_estadual' => '092969305',
            'endereco_id' => 1,
            'responsavel_tecnico_id' => 1,
        ]);

        DB::table('empresa_preferencias')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'ambiente_dfe' => 2,
            'numero_ultima_nota' => 10,
            'serie' => '1',
            'cfop_padrao' => '5102',
            'regime_tributario' => 'Simples Nacional',
        ]);

        DB::table('cfops')->insert([
            'id' => 1,
            'cfop' => '5102',
            'natureza' => 'VENDA DE MERCADORIA',
        ]);

        DB::table('ncms')->insert([
            'id' => 1,
            'ncm' => '21069090',
            'descricao' => 'Preparacoes alimenticias',
        ]);

        DB::table('clientes')->insert([
            'id' => 1,
            'nome_razao_social' => 'HOSPEDE TESTE',
            'cpf_cnpj' => '12345678909',
            'endereco_id' => 1,
        ]);

        DB::table('produtos')->insert([
            'id' => 1,
            'descricao' => 'REFEICAO ALMOCO',
            'preco_venda' => 35.0,
            'ncm' => '21069090',
            'cfop_interno' => '5102',
            'empresa_id' => 1,
        ]);
    }

    public function test_grava_nota_fiscal_itens_e_gera_xml_com_sucesso(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $payload = [
            'empresa_id' => 1,
            'numero' => 11,
            'serie' => 1,
            'data_emissao' => '2026-09-25',
            'tipo_nota' => 1,
            'cfop' => '5102',
            'cliente' => ['id' => 1, 'razao_social' => 'HOSPEDE TESTE'],
            'itens' => [
                [
                    'produto_id' => 1,
                    'produto' => 'REFEICAO ALMOCO',
                    'quantidade' => 2,
                    'valor_unitario' => 35.0,
                    'subtotal' => 70.0,
                    'desconto' => 5.0,
                    'total' => 65.0,
                    'ncm' => '21069090',
                    'cfop' => '5102',
                    'csosn' => '102',
                    'cst' => '00',
                    'base_calculo' => 65.0,
                    'valor_icms' => 0.0,
                ],
            ],
            'subtotal' => 70.0,
            'desconto' => 5.0,
            'total' => 65.0,
            'forma_pagamento' => '17',
            'informacoes_complementares' => 'TESTE NOTA FISCAL',
        ];

        $request = new Request($payload);
        $request->headers->set('Accept', 'application/json');

        $controller = new NotaFiscalController;
        $response = $controller->store($request);

        // 1. Verifica inserção no banco da tabela nota_fiscals
        $this->assertDatabaseHas('nota_fiscals', [
            'numero' => 11,
            'serie' => 1,
            'cliente_id' => 1,
            'total_nota' => 65.0,
            'total_produtos' => 70.0,
            'total_desconto' => 5.0,
        ]);

        // 2. Verifica inserção dos itens na tabela nota_fiscal_itens
        $nota = NotaFiscal::where('numero', 11)->first();
        $this->assertNotNull($nota);
        $this->assertDatabaseHas('nota_fiscal_itens', [
            'nota_fiscal_id' => $nota->id,
            'produto_id' => 1,
            'quantidade' => 2,
            'v_unitario' => 35.0,
            'total' => 65.0,
        ]);

        // 3. Verifica se a chave de acesso de 44 dígitos foi gerada e gravada
        $this->assertNotNull($nota->chave);
        $this->assertEquals(44, strlen($nota->chave));

        // 4. Verifica se o arquivo XML físico foi criado na pasta storage/app/nfe/geradas/
        $caminhoXml = storage_path('app/nfe/geradas/'.$nota->chave.'.xml');
        $this->assertFileExists($caminhoXml);
        $conteudoXml = file_get_contents($caminhoXml);
        $this->assertStringContainsString('<mod>55</mod>', $conteudoXml);
        $this->assertStringContainsString('<nNF>11</nNF>', $conteudoXml);

        // 5. Verifica download do XML gerado
        $downloadResponse = $controller->baixarXml((string) $nota->id);
        $this->assertNotNull($downloadResponse);

        // Limpeza do arquivo de teste gerado
        if (File::exists($caminhoXml)) {
            File::delete($caminhoXml);
        }
    }

    public function test_redirecionamento_e_mensagem_de_sucesso_via_requisicao_web(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $payload = [
            'empresa_id' => 1,
            'numero' => 12,
            'serie' => 1,
            'data_emissao' => '2026-09-25',
            'tipo_nota' => 1,
            'cfop' => '5102',
            'cliente' => ['id' => 1, 'razao_social' => 'HOSPEDE TESTE'],
            'itens' => [
                [
                    'produto_id' => 1,
                    'produto' => 'REFEICAO ALMOCO',
                    'quantidade' => 1,
                    'valor_unitario' => 35.0,
                    'subtotal' => 35.0,
                    'desconto' => 0.0,
                    'total' => 35.0,
                    'ncm' => '21069090',
                    'cfop' => '5102',
                    'csosn' => '102',
                    'cst' => '00',
                    'base_calculo' => 35.0,
                    'valor_icms' => 0.0,
                ],
            ],
            'subtotal' => 35.0,
            'desconto' => 0.0,
            'total' => 35.0,
            'forma_pagamento' => '01',
        ];

        $request = new Request($payload);
        $controller = new NotaFiscalController;
        $response = $controller->store($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('nota_fiscal.index'), $response->getTargetUrl());
        $this->assertTrue(session()->has('success'));

        $nota = NotaFiscal::where('numero', 12)->first();
        $this->assertNotNull($nota);
        $this->assertNotNull($nota->chave);

        $caminhoXml = storage_path('app/nfe/geradas/'.$nota->chave.'.xml');
        if (File::exists($caminhoXml)) {
            File::delete($caminhoXml);
        }
    }

    public function test_exibe_detalhes_da_nota_fiscal_com_sucesso(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 13,
            'total_produtos' => 35.0,
            'total_nota' => 35.0,
        ]);

        NotaFiscalItem::create([
            'nota_fiscal_id' => $nota->id,
            'produto_id' => 1,
            'quantidade' => 1,
            'v_unitario' => 35.0,
            'subtotal' => 35.0,
            'total' => 35.0,
            'cfop_id' => 1,
            'csosm' => '102',
        ]);

        $controller = new NotaFiscalController;
        $response = $controller->show((string) $nota->id);

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('notasFiscaisDetalhes', $response->name());
        $this->assertArrayHasKey('nota', $response->getData());
        $this->assertEquals(13, $response->getData()['nota']->numero);
    }

    public function test_gerar_xml_manual_para_nota_sem_chave(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 14,
            'chave' => null,
            'total_produtos' => 35.0,
            'total_nota' => 35.0,
        ]);

        NotaFiscalItem::create([
            'nota_fiscal_id' => $nota->id,
            'produto_id' => 1,
            'quantidade' => 1,
            'v_unitario' => 35.0,
            'subtotal' => 35.0,
            'total' => 35.0,
            'cfop_id' => 1,
            'csosm' => '102',
        ]);

        $controller = new NotaFiscalController;
        $response = $controller->gerarXmlManual((string) $nota->id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));

        $nota->refresh();
        $this->assertNotNull($nota->chave);
        $this->assertEquals(44, strlen($nota->chave));

        $caminhoXml = storage_path('app/nfe/geradas/'.$nota->chave.'.xml');
        $this->assertFileExists($caminhoXml);
        if (File::exists($caminhoXml)) {
            File::delete($caminhoXml);
        }
    }

    public function test_exclusao_de_nota_fiscal(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 15,
            'total_produtos' => 50.0,
            'total_nota' => 50.0,
        ]);

        $controller = new NotaFiscalController;
        $response = $controller->destroy((string) $nota->id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertDatabaseMissing('nota_fiscals', ['id' => $nota->id]);
        $this->assertTrue(session()->has('success'));
    }

    public function test_controller_assinar_nota_fiscal_com_sucesso(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 25,
            'total_produtos' => 35.0,
            'total_nota' => 35.0,
        ]);

        NotaFiscalItem::create([
            'nota_fiscal_id' => $nota->id,
            'produto_id' => 1,
            'quantidade' => 1,
            'v_unitario' => 35.0,
            'subtotal' => 35.0,
            'total' => 35.0,
            'cfop_id' => 1,
            'csosm' => '102',
        ]);

        // Mock ou instancia de NFeService com Tools
        $dn = [
            'countryName' => 'BR',
            'stateOrProvinceName' => 'PE',
            'localityName' => 'Garanhuns',
            'organizationName' => 'HOTEL LAGO LTDA:38090491000181',
            'commonName' => 'HOTEL LAGO LTDA:38090491000181',
        ];
        $configArgs = [];
        $possiveisCnf = ['C:/php84/extras/ssl/openssl.cnf', 'C:/Program Files/Git/mingw64/etc/ssl/openssl.cnf'];
        foreach ($possiveisCnf as $cnf) {
            if (file_exists($cnf)) {
                $configArgs = ['config' => $cnf];
                break;
            }
        }
        $privkey = openssl_pkey_new(array_merge(['private_key_bits' => 1024, 'private_key_type' => OPENSSL_KEYTYPE_RSA], $configArgs));
        $csr = openssl_csr_new($dn, $privkey, array_merge(['digest_alg' => 'sha256'], $configArgs));
        $x509 = openssl_csr_sign($csr, null, $privkey, 365, array_merge(['digest_alg' => 'sha256'], $configArgs));
        $pfx = '';
        openssl_pkcs12_export($x509, $pfx, $privkey, '123456');

        $cert = Certificate::readPfx($pfx, '123456');
        $tools = new Tools(json_encode([
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => 2,
            'razaosocial' => 'HOTEL LAGO LTDA',
            'siglaUF' => 'PE',
            'cnpj' => '38090491000181',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => '',
            'CSC' => '',
            'CSCid' => '',
        ]), $cert);
        $tools->model('55');

        $nfeService = app(NFeService::class);
        $nfeService->setTools($tools);
        $this->app->instance(NFeService::class, $nfeService);

        $controller = new NotaFiscalController;
        $response = $controller->assinar((string) $nota->id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));

        $nota->refresh();
        $this->assertNotNull($nota->chave);
        $this->assertTrue($nota->isAssinada());

        // Limpeza dos arquivos gerados
        $caminhoAssinado = storage_path('app/nfe/assinadas/'.$nota->chave.'.xml');
        if (File::exists($caminhoAssinado)) {
            File::delete($caminhoAssinado);
        }
        $caminhoGerado = storage_path('app/nfe/geradas/'.$nota->chave.'.xml');
        if (File::exists($caminhoGerado)) {
            File::delete($caminhoGerado);
        }
    }

    public function test_controller_verificar_certificado_retorna_feedback(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $controller = new NotaFiscalController;
        $response = $controller->verificarCertificado();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('error') || session()->has('success'));
    }

    public function test_controller_transmitir_nota_fiscal_autorizada(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 26,
            'total_produtos' => 35.0,
            'total_nota' => 35.0,
        ]);

        $mockNFeService = Mockery::mock(NFeService::class);
        $mockNFeService->shouldReceive('transmitirNotaFiscal')
            ->once()
            ->andReturn([
                'sucesso' => true,
                'autorizada' => true,
                'protocolo' => '126240001234567',
                'chave' => '26260938090491000181550010000000261420590740',
                'caminho' => storage_path('app/nfe/autorizadas/dummy.xml'),
            ]);

        $this->app->instance(NFeService::class, $mockNFeService);

        $controller = new NotaFiscalController;
        $response = $controller->transmitir((string) $nota->id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('success'));
        $this->assertStringContainsString('AUTORIZADA', session('success'));
        $this->assertStringContainsString('126240001234567', session('success'));
    }

    public function test_controller_transmitir_nota_fiscal_com_erro(): void
    {
        $user = new User(['id' => 1, 'name' => 'Admin', 'email' => 'admin@teste.com', 'password' => 'secret', 'empresa_id' => 1]);
        Auth::setUser($user);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 27,
            'total_produtos' => 35.0,
            'total_nota' => 35.0,
        ]);

        $mockNFeService = Mockery::mock(NFeService::class);
        $mockNFeService->shouldReceive('transmitirNotaFiscal')
            ->once()
            ->andReturn([
                'sucesso' => false,
                'autorizada' => false,
                'rejeitada' => true,
                'erro' => 'Rejeição: Duplicidade de NF-e',
            ]);

        $this->app->instance(NFeService::class, $mockNFeService);

        $controller = new NotaFiscalController;
        $response = $controller->transmitir((string) $nota->id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('Duplicidade', session('error'));
    }
}
