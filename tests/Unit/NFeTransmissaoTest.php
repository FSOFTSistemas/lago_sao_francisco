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
use Mockery;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use Tests\TestCase;

class NFeTransmissaoTest extends TestCase
{
    private static ?string $pfxCache = null;

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

        EmpresaPreferencia::create([
            'empresa_id' => 1,
            'ambiente_dfe' => 2,
            'numero_ultima_nota' => 1,
            'serie' => '1',
            'cfop_padrao' => '5102',
            'regime_tributario' => '1',
        ]);

        Cliente::create([
            'id' => 1,
            'nome_razao_social' => 'CLIENTE TESTE TRANSMISSAO',
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

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

    protected function gerarXmlAssinadoTeste(Empresa $empresa): array
    {
        $nfeService = new NFeService;
        $pfx = $this->gerarPfxTeste('123456');
        $cert = Certificate::readPfx($pfx, '123456');

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
        $nfeService->setTools($tools);

        $dados = [
            'empresa_id' => 1,
            'numero' => 31,
            'serie' => 1,
            'data_emissao' => '2026-09-25 14:00:00',
            'tipo_nota' => 1,
            'cfop' => '5102',
            'cliente' => ['id' => 1, 'razao_social' => 'CLIENTE TESTE TRANSMISSAO'],
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

        $resXml = $nfeService->gerarXml($dados, $empresa);
        $resAssinatura = $nfeService->assinarXml($resXml['xml'], $empresa);

        return [
            'chave' => $resAssinatura['chave'],
            'xml_assinado' => $resAssinatura['xml'],
        ];
    }

    public function test_transmissao_sincrona_autorizada_gera_proc_nfe_com_sucesso(): void
    {
        $empresa = Empresa::find(1);
        $gerado = $this->gerarXmlAssinadoTeste($empresa);
        $chave = $gerado['chave'];
        $signXml = $gerado['xml_assinado'];

        preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $signXml, $dMatches);
        $digVal = $dMatches[1] ?? '';

        // Resposta da SEFAZ para autorização síncrona
        $xmlRetSefaz = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <nProt>126240001234567</nProt>
                    <digVal>'.$digVal.'</digVal>
                    <cStat>100</cStat>
                    <xMotivo>Autorizado o uso da NF-e</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')
            ->once()
            ->andReturn($xmlRetSefaz);

        $nfeService = new NFeService($mockTools);
        $resultado = $nfeService->transmitir($signXml, $chave, $empresa, 1);
        $this->assertTrue($resultado['sucesso']);
        $this->assertTrue($resultado['autorizada']);
        $this->assertEquals('100', $resultado['cStat']);
        $this->assertEquals('126240001234567', $resultado['protocolo']);
        $this->assertFileExists($resultado['caminho']);

        // Verifica a estrutura do XML autorizado gerado pelo Complements::toAuthorize
        $xmlAutorizado = file_get_contents($resultado['caminho']);
        $this->assertStringContainsString('<nfeProc', $xmlAutorizado);
        $this->assertStringContainsString('<protNFe', $xmlAutorizado);
        $this->assertStringContainsString('<nProt>126240001234567</nProt>', $xmlAutorizado);

        // Limpeza do arquivo gerado
        if (File::exists($resultado['caminho'])) {
            File::delete($resultado['caminho']);
        }
    }

    public function test_transmissao_assincrona_autorizada_apos_consulta_recibo(): void
    {
        $empresa = Empresa::find(1);
        $gerado = $this->gerarXmlAssinadoTeste($empresa);
        $chave = $gerado['chave'];
        $signXml = $gerado['xml_assinado'];

        preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $signXml, $dMatches);
        $digVal = $dMatches[1] ?? '';

        // Resposta 1: Lote recebido assincronamente (cStat 103)
        $xmlRetLote = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>103</cStat>
            <xMotivo>Lote recebido com sucesso</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <infRec>
                <nRec>261000123456789</nRec>
                <tMed>1</tMed>
            </infRec>
        </retEnviNFe>';

        // Resposta 2: Consulta do recibo autorizada (cStat 104)
        $xmlRetConsulta = '<?xml version="1.0" encoding="UTF-8"?>
        <retConsReciNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <nRec>261000123456789</nRec>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:02-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:02-03:00</dhRecbto>
                    <nProt>126240009999999</nProt>
                    <digVal>'.$digVal.'</digVal>
                    <cStat>100</cStat>
                    <xMotivo>Autorizado o uso da NF-e</xMotivo>
                </infProt>
            </protNFe>
        </retConsReciNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')
            ->once()
            ->andReturn($xmlRetLote);

        $mockTools->shouldReceive('sefazConsultaRecibo')
            ->once()
            ->with('261000123456789')
            ->andReturn($xmlRetConsulta);

        $nfeService = new NFeService($mockTools);
        $resultado = $nfeService->transmitir($signXml, $chave, $empresa, 0);

        $this->assertTrue($resultado['sucesso']);
        $this->assertTrue($resultado['autorizada']);
        $this->assertEquals('126240009999999', $resultado['protocolo']);
        $this->assertEquals('261000123456789', $resultado['recibo']);
        $this->assertFileExists($resultado['caminho']);

        if (File::exists($resultado['caminho'])) {
            File::delete($resultado['caminho']);
        }
    }

    public function test_transmissao_com_rejeicao_sefaz(): void
    {
        $empresa = Empresa::find(1);
        $gerado = $this->gerarXmlAssinadoTeste($empresa);
        $chave = $gerado['chave'];
        $signXml = $gerado['xml_assinado'];

        $xmlRejeicao = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <cStat>204</cStat>
                    <xMotivo>Rejeicao: Duplicidade de NF-e</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')->once()->andReturn($xmlRejeicao);

        $nfeService = new NFeService($mockTools);
        $resultado = $nfeService->transmitir($signXml, $chave, $empresa, 1);

        $this->assertFalse($resultado['sucesso']);
        $this->assertFalse($resultado['autorizada']);
        $this->assertTrue($resultado['rejeitada']);
        $this->assertEquals('204', $resultado['cStat']);
        $this->assertStringContainsString('Duplicidade', $resultado['erro']);
    }

    public function test_transmissao_denegada_sefaz(): void
    {
        $empresa = Empresa::find(1);
        $gerado = $this->gerarXmlAssinadoTeste($empresa);
        $chave = $gerado['chave'];
        $signXml = $gerado['xml_assinado'];

        $xmlDenegada = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <cStat>302</cStat>
                    <xMotivo>Uso Denegado: Irregularidade fiscal do destinatario</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')->once()->andReturn($xmlDenegada);

        $nfeService = new NFeService($mockTools);
        $resultado = $nfeService->transmitir($signXml, $chave, $empresa, 1);

        $this->assertFalse($resultado['sucesso']);
        $this->assertTrue($resultado['denegada']);
        $this->assertEquals('302', $resultado['cStat']);
        $this->assertStringContainsString('Denegado', $resultado['erro']);
    }

    public function test_transmitir_nota_fiscal_completa(): void
    {
        $empresa = Empresa::find(1);

        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 32,
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

        $pfx = $this->gerarPfxTeste('123456');
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

        $nfeService = new NFeService($tools);

        // 1. Assina a nota fiscal primeiro
        $resAssinatura = $nfeService->assinarNotaFiscal($nota);
        $chave = $resAssinatura['chave'];

        $xmlAssinado = file_get_contents($resAssinatura['caminho']);
        preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $xmlAssinado, $dMatches);
        $digVal = $dMatches[1] ?? '';

        // 2. Mock do envio síncrono para a SEFAZ
        $mockTools = Mockery::mock(Tools::class);
        $xmlRetSefaz = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <nProt>126240007777777</nProt>
                    <digVal>'.$digVal.'</digVal>
                    <cStat>100</cStat>
                    <xMotivo>Autorizado o uso da NF-e</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools->shouldReceive('sefazEnviaLote')->once()->andReturn($xmlRetSefaz);
        $nfeService->setTools($mockTools);

        // 3. Executa a transmissão da nota
        $resultado = $nfeService->transmitirNotaFiscal($nota, 1);

        $this->assertTrue($resultado['sucesso']);
        $this->assertTrue($resultado['autorizada']);
        $this->assertEquals('126240007777777', $resultado['protocolo']);
        $this->assertFileExists($resultado['caminho']);

        // Verifica persistência no banco de dados e helpers do modelo NotaFiscal
        $nota->refresh();
        $this->assertEquals(NotaFiscal::STATUS_AUTORIZADA, $nota->status);
        $this->assertEquals('126240007777777', $nota->protocolo);
        $this->assertEquals('100', $nota->cstat);
        $this->assertEquals('Autorizado o uso da NF-e', $nota->motivo_status);
        $this->assertNotNull($nota->data_autorizacao);
        $this->assertTrue($nota->isAutorizada());
        $this->assertEquals('Autorizada', $nota->status_formatado);
        $this->assertEquals('bg-success', $nota->status_badge_class);

        // Limpeza dos arquivos gerados
        if (File::exists($resultado['caminho'])) {
            File::delete($resultado['caminho']);
        }
        $caminhoAssinado = storage_path('app/nfe/assinadas/'.$chave.'.xml');
        if (File::exists($caminhoAssinado)) {
            File::delete($caminhoAssinado);
        }
        $caminhoGerado = storage_path('app/nfe/geradas/'.$chave.'.xml');
        if (File::exists($caminhoGerado)) {
            File::delete($caminhoGerado);
        }
    }

    public function test_transmitir_nota_fiscal_rejeitada_persiste_status_e_motivo(): void
    {
        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 33,
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

        $pfx = $this->gerarPfxTeste('123456');
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

        $nfeService = new NFeService($tools);
        $resAssinatura = $nfeService->assinarNotaFiscal($nota);
        $chave = $resAssinatura['chave'];

        $xmlRejeicao = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <cStat>204</cStat>
                    <xMotivo>Rejeicao: Duplicidade de NF-e</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')->once()->andReturn($xmlRejeicao);
        $nfeService->setTools($mockTools);

        $resultado = $nfeService->transmitirNotaFiscal($nota, 1);

        $this->assertFalse($resultado['sucesso']);
        $this->assertTrue($resultado['rejeitada']);

        $nota->refresh();
        $this->assertEquals(NotaFiscal::STATUS_REJEITADA, $nota->status);
        $this->assertEquals('204', $nota->cstat);
        $this->assertStringContainsString('Duplicidade', $nota->motivo_status);
        $this->assertTrue($nota->isRejeitada());
        $this->assertEquals('Rejeitada', $nota->status_formatado);
        $this->assertEquals('bg-danger', $nota->status_badge_class);

        $caminhoAssinado = storage_path('app/nfe/assinadas/'.$chave.'.xml');
        if (File::exists($caminhoAssinado)) {
            File::delete($caminhoAssinado);
        }
        $caminhoGerado = storage_path('app/nfe/geradas/'.$chave.'.xml');
        if (File::exists($caminhoGerado)) {
            File::delete($caminhoGerado);
        }
    }

    public function test_transmitir_nota_fiscal_denegada_persiste_status_e_motivo(): void
    {
        $nota = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'empresa_id' => 1,
            'data' => '2026-09-25',
            'serie' => 1,
            'numero' => 34,
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

        $pfx = $this->gerarPfxTeste('123456');
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

        $nfeService = new NFeService($tools);
        $resAssinatura = $nfeService->assinarNotaFiscal($nota);
        $chave = $resAssinatura['chave'];

        $xmlDenegada = '<?xml version="1.0" encoding="UTF-8"?>
        <retEnviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
            <tpAmb>2</tpAmb>
            <verAplic>SVRS202609</verAplic>
            <cStat>104</cStat>
            <xMotivo>Lote processado</xMotivo>
            <cUF>26</cUF>
            <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
            <protNFe versao="4.00">
                <infProt>
                    <tpAmb>2</tpAmb>
                    <verAplic>SVRS202609</verAplic>
                    <chNFe>'.$chave.'</chNFe>
                    <dhRecbto>2026-09-25T14:00:00-03:00</dhRecbto>
                    <cStat>302</cStat>
                    <xMotivo>Uso Denegado: Irregularidade fiscal do destinatario</xMotivo>
                </infProt>
            </protNFe>
        </retEnviNFe>';

        $mockTools = Mockery::mock(Tools::class);
        $mockTools->shouldReceive('sefazEnviaLote')->once()->andReturn($xmlDenegada);
        $nfeService->setTools($mockTools);

        $resultado = $nfeService->transmitirNotaFiscal($nota, 1);

        $this->assertFalse($resultado['sucesso']);
        $this->assertTrue($resultado['denegada']);

        $nota->refresh();
        $this->assertEquals(NotaFiscal::STATUS_DENEGADA, $nota->status);
        $this->assertEquals('302', $nota->cstat);
        $this->assertStringContainsString('Denegado', $nota->motivo_status);
        $this->assertTrue($nota->isDenegada());
        $this->assertEquals('Uso Denegado', $nota->status_formatado);
        $this->assertEquals('bg-dark', $nota->status_badge_class);

        $caminhoAssinado = storage_path('app/nfe/assinadas/'.$chave.'.xml');
        if (File::exists($caminhoAssinado)) {
            File::delete($caminhoAssinado);
        }
        $caminhoGerado = storage_path('app/nfe/geradas/'.$chave.'.xml');
        if (File::exists($caminhoGerado)) {
            File::delete($caminhoGerado);
        }
    }
}
