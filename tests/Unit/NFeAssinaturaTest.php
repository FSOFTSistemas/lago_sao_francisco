<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use App\Models\Endereco;
use App\Models\NotaFiscal;
use App\Models\NotaFiscalItem;
use App\Models\Produto;
use App\Services\NFeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use Tests\TestCase;

class NFeAssinaturaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

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

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('certificado_digital')->nullable();
            $table->text('senha_certificado')->nullable();
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

        Schema::create('nota_fiscals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('ncm_id')->default(1);
            $table->unsignedBigInteger('cfop_id')->default(1);
            $table->unsignedBigInteger('usuario_id')->default(1);
            $table->unsignedBigInteger('empresa_id')->default(1);
            $table->date('data');
            $table->string('chave')->nullable();
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

        // Endereço e Empresa
        Endereco::create([
            'id' => 1,
            'logradouro' => 'Rua Principal',
            'numero' => '100',
            'bairro' => 'Centro',
            'cidade' => 'Garanhuns',
            'uf' => 'PE',
            'cep' => '55290000',
            'ibge' => '2606002',
        ]);

        Empresa::create([
            'id' => 1,
            'razao_social' => 'HOTEL LAGO LTDA',
            'nome_fantasia' => 'HOTEL LAGO',
            'cnpj' => '38090491000181',
            'inscricao_estadual' => '092969305',
            'endereco_id' => 1,
        ]);

        Cliente::create([
            'id' => 1,
            'nome_razao_social' => 'CLIENTE DE TESTES',
            'cpf_cnpj' => '12345678909',
            'endereco_id' => 1,
        ]);

        Produto::create([
            'id' => 1,
            'descricao' => 'REFEICAO COMPLETA',
            'preco_venda' => 45.0,
            'ncm' => '21069090',
            'cfop_interno' => '5102',
            'empresa_id' => 1,
        ]);
    }

    private static ?string $pfxCache = null;

    protected function gerarPfxTeste(string $senha = '123456'): string
    {
        if (self::$pfxCache !== null) {
            return self::$pfxCache;
        }

        $dn = [
            'countryName' => 'BR',
            'stateOrProvinceName' => 'PE',
            'localityName' => 'Garanhuns',
            'organizationName' => 'HOTEL LAGO LTDA:38090491000181',
            'commonName' => 'HOTEL LAGO LTDA:38090491000181',
        ];

        $configArgs = [];
        $possiveisCnf = [
            'C:/php84/extras/ssl/openssl.cnf',
            'C:/Program Files/Git/mingw64/etc/ssl/openssl.cnf',
            getenv('OPENSSL_CONF'),
        ];
        foreach ($possiveisCnf as $cnf) {
            if ($cnf && file_exists($cnf)) {
                $configArgs = ['config' => $cnf];
                break;
            }
        }

        $privkey = openssl_pkey_new(array_merge(['private_key_bits' => 1024, 'private_key_type' => OPENSSL_KEYTYPE_RSA], $configArgs));
        $csr = openssl_csr_new($dn, $privkey, array_merge(['digest_alg' => 'sha256'], $configArgs));
        $x509 = openssl_csr_sign($csr, null, $privkey, 365, array_merge(['digest_alg' => 'sha256'], $configArgs));
        $pfx = '';
        openssl_pkcs12_export($x509, $pfx, $privkey, $senha);

        self::$pfxCache = $pfx;

        return self::$pfxCache;
    }

    protected function criarToolsComCertificado(string $pfx, string $senha = '123456'): Tools
    {
        $cert = Certificate::readPfx($pfx, $senha);
        $config = [
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
        ];

        $tools = new Tools(json_encode($config), $cert);
        $tools->model('55');

        return $tools;
    }

    public function test_verificar_certificado_sem_preferencias_retorna_erro(): void
    {
        $empresa = Empresa::find(1);
        $nfeService = new NFeService;

        $resultado = $nfeService->verificarCertificado($empresa);

        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('preferências fiscais', $resultado['erro']);
    }

    public function test_verificar_certificado_com_arquivo_inexistente_retorna_erro(): void
    {
        $empresa = Empresa::find(1);
        EmpresaPreferencia::create([
            'empresa_id' => 1,
            'certificado_digital' => 'arquivo_que_nao_existe.pfx',
            'senha_certificado' => '123456',
        ]);

        $nfeService = new NFeService;
        $resultado = $nfeService->verificarCertificado($empresa);

        $this->assertFalse($resultado['valido']);
        $this->assertStringContainsString('não localizado', $resultado['erro']);
    }

    public function test_verificar_certificado_valido_com_pfx(): void
    {
        $pfx = $this->gerarPfxTeste('123456');
        $caminhoTemp = storage_path('app/certificados_teste_'.uniqid().'.pfx');
        File::put($caminhoTemp, $pfx);

        $empresa = Empresa::find(1);
        EmpresaPreferencia::create([
            'empresa_id' => 1,
            'certificado_digital' => $caminhoTemp,
            'senha_certificado' => '123456',
        ]);

        $nfeService = new NFeService;
        $resultado = $nfeService->verificarCertificado($empresa);

        $this->assertTrue($resultado['valido']);
        $this->assertFalse($resultado['expirado']);
        $this->assertArrayHasKey('dados', $resultado);
        $this->assertGreaterThan(0, $resultado['dados']['dias_restantes']);

        if (File::exists($caminhoTemp)) {
            File::delete($caminhoTemp);
        }
    }

    public function test_assinar_xml_com_sucesso(): void
    {
        $empresa = Empresa::find(1);
        EmpresaPreferencia::create([
            'empresa_id' => 1,
            'ambiente_dfe' => 2,
            'numero_ultima_nota' => 1,
            'serie' => '1',
            'cfop_padrao' => '5102',
            'regime_tributario' => '1',
        ]);

        $nfeService = new NFeService;

        // 1. Gera XML não assinado
        $dados = [
            'empresa_id' => 1,
            'numero' => 21,
            'serie' => 1,
            'data_emissao' => '2026-09-25 12:00:00',
            'tipo_nota' => 1,
            'cfop' => '5102',
            'cliente' => ['id' => 1, 'razao_social' => 'CLIENTE DE TESTES'],
            'itens' => [
                [
                    'produto_id' => 1,
                    'produto' => 'REFEICAO COMPLETA',
                    'quantidade' => 1,
                    'valor_unitario' => 45.0,
                    'subtotal' => 45.0,
                    'desconto' => 0.0,
                    'total' => 45.0,
                    'ncm' => '21069090',
                    'cfop' => '5102',
                    'csosn' => '102',
                    'cst' => '00',
                    'base_calculo' => 45.0,
                    'valor_icms' => 0.0,
                ],
            ],
            'subtotal' => 45.0,
            'desconto' => 0.0,
            'total' => 45.0,
            'forma_pagamento' => '01',
        ];

        $xmlGerado = $nfeService->gerarXml($dados, $empresa);
        $this->assertTrue($xmlGerado['sucesso']);

        // 2. Configura Tools com certificado A1 de teste
        $pfx = $this->gerarPfxTeste('123456');
        $tools = $this->criarToolsComCertificado($pfx, '123456');
        $nfeService->setTools($tools);

        // 3. Assina o XML e valida contra XSD oficial
        $resultado = $nfeService->assinarXml($xmlGerado['xml'], $empresa);

        $this->assertTrue($resultado['sucesso']);
        $this->assertNotEmpty($resultado['xml']);
        $this->assertEquals(44, strlen($resultado['chave']));

        // Verifica elementos da assinatura digital XML-DSig
        $this->assertStringContainsString('<Signature', $resultado['xml']);
        $this->assertStringContainsString('<SignedInfo>', $resultado['xml']);
        $this->assertStringContainsString('<DigestValue>', $resultado['xml']);
        $this->assertStringContainsString('<SignatureValue>', $resultado['xml']);
        $this->assertStringContainsString('<X509Certificate>', $resultado['xml']);
    }

    public function test_assinar_xml_invalido_rejeitado_pelo_xsd(): void
    {
        $empresa = Empresa::find(1);
        $nfeService = new NFeService;

        $pfx = $this->gerarPfxTeste('123456');
        $tools = $this->criarToolsComCertificado($pfx, '123456');
        $nfeService->setTools($tools);

        $resultado = $nfeService->assinarXml('<xml>invalido</xml>', $empresa, true);

        $this->assertFalse($resultado['sucesso']);
        $this->assertStringContainsString('Falha na assinatura digital', $resultado['erro']);
    }

    public function test_assinar_nota_fiscal_grava_xml_assinado_no_storage(): void
    {
        $empresa = Empresa::find(1);
        EmpresaPreferencia::create([
            'empresa_id' => 1,
            'ambiente_dfe' => 2,
            'numero_ultima_nota' => 1,
            'serie' => '1',
            'cfop_padrao' => '5102',
            'regime_tributario' => '1',
        ]);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 22,
            'total_produtos' => 45.0,
            'total_nota' => 45.0,
        ]);

        NotaFiscalItem::create([
            'nota_fiscal_id' => $nota->id,
            'produto_id' => 1,
            'quantidade' => 1,
            'v_unitario' => 45.0,
            'subtotal' => 45.0,
            'total' => 45.0,
            'cfop_id' => 1,
            'csosm' => '102',
        ]);

        $nfeService = new NFeService;
        $pfx = $this->gerarPfxTeste('123456');
        $tools = $this->criarToolsComCertificado($pfx, '123456');
        $nfeService->setTools($tools);

        // Assina a NotaFiscal diretamente
        $resultado = $nfeService->assinarNotaFiscal($nota);

        $this->assertTrue($resultado['sucesso']);
        $this->assertNotNull($resultado['chave']);
        $this->assertFileExists($resultado['caminho']);

        // Verifica helpers do modelo NotaFiscal
        $this->assertTrue($nota->isGerada());
        $this->assertTrue($nota->isAssinada());
        $this->assertEquals('Assinada', $nota->status_formatado);

        // Limpeza dos arquivos gerados
        if (File::exists($resultado['caminho'])) {
            File::delete($resultado['caminho']);
        }
        $caminhoGerado = storage_path('app/nfe/geradas/'.$resultado['chave'].'.xml');
        if (File::exists($caminhoGerado)) {
            File::delete($caminhoGerado);
        }
    }
}
