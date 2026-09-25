<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\EmpresaPreferencia;
use App\Models\EmpresaRT;
use App\Services\NFeService;
use Tests\TestCase;

class NFeXmlBuilderTest extends TestCase
{
    public function test_gerar_xml_de_nfe_com_sucesso(): void
    {
        $service = new NFeService();

        // Cria instância em memória da empresa e preferência para simular dados
        $endereco = new Endereco([
            'logradouro' => 'Rua Principal',
            'numero'     => '100',
            'bairro'     => 'Centro',
            'cidade'     => 'Garanhuns',
            'uf'         => 'PE',
            'cep'        => '55290000',
            'ibge'       => '2606002',
        ]);

        $preferencia = new EmpresaPreferencia([
            'ambiente_dfe'       => 2, // Homologação
            'numero_ultima_nota' => 10,
            'serie'              => '1',
            'cfop_padrao'        => '5102',
            'regime_tributario'  => 'Simples Nacional',
        ]);

        $rt = new EmpresaRT([
            'nome'     => 'Fsoft Sistemas LTDA',
            'cnpj'     => '60177690000180',
            'telefone' => '87999999999',
            'email'    => 'contato@fsoft.com.br',
        ]);

        $empresa = new Empresa([
            'razao_social'       => 'HOTEL LAGO SAO FRANCISCO LTDA',
            'nome_fantasia'      => 'HOTEL LAGO',
            'cnpj'               => '38090491000181',
            'inscricao_estadual' => '092969305',
        ]);
        $empresa->setRelation('endereco', $endereco);
        $empresa->setRelation('preferencia', $preferencia);
        $empresa->setRelation('responsavelTecnico', $rt);

        $dadosNfe = [
            'numero'          => 11,
            'serie'           => 1,
            'natureza_operacao' => 'VENDA DE MERCADORIA',
            'tipo_nota'       => 'saida',
            'finalidade'      => 1,
            'cliente'         => [
                'razao_social' => 'JOAO DA SILVA',
                'cpf_cnpj'     => '12345678909',
            ],
            'itens'           => [
                [
                    'produto_id'     => 1,
                    'produto'        => 'AGUA MINERAL 500ML',
                    'ncm'            => '22011000',
                    'cfop'           => '5102',
                    'un'             => 'UN',
                    'quantidade'     => 2,
                    'valor_unitario' => 5.00,
                    'desconto'       => 0.00,
                    'csosn'          => '102',
                ],
                [
                    'produto_id'     => 2,
                    'produto'        => 'REFRIGERANTE LATA',
                    'ncm'            => '22021000',
                    'cfop'           => '5102',
                    'un'             => 'UN',
                    'quantidade'     => 1,
                    'valor_unitario' => 7.00,
                    'desconto'       => 1.00,
                    'csosn'          => '500',
                ],
            ],
            'forma_pagamento' => '17', // PIX
            'info_complementares' => 'Documento emitido por ME ou EPP optante pelo Simples Nacional',
        ];

        $resultado = $service->gerarXml($dadosNfe, $empresa);

        $this->assertTrue($resultado['sucesso'], 'Falha na geração do XML: ' . json_encode($resultado['erros'] ?? []));
        $this->assertNotEmpty($resultado['xml']);
        $this->assertNotEmpty($resultado['chave']);
        $this->assertEquals(44, strlen($resultado['chave']));
        $this->assertEquals(11, $resultado['nNF']);
        $this->assertEquals(1, $resultado['serie']);

        // Verifica tags essenciais no XML gerado
        $xml = $resultado['xml'];
        $this->assertStringContainsString('<mod>55</mod>', $xml);
        $this->assertStringContainsString('<nNF>11</nNF>', $xml);
        $this->assertStringContainsString('<CNPJ>38090491000181</CNPJ>', $xml);
        $this->assertStringContainsString('<xNome>HOTEL LAGO SAO FRANCISCO LTDA</xNome>', $xml);
        $this->assertStringContainsString('<xProd>AGUA MINERAL 500ML</xProd>', $xml);
        $this->assertStringContainsString('<xProd>REFRIGERANTE LATA</xProd>', $xml);
        $this->assertStringContainsString('<vProd>17.00</vProd>', $xml);
        $this->assertStringContainsString('<vDesc>1.00</vDesc>', $xml);
        $this->assertStringContainsString('<vNF>16.00</vNF>', $xml);
        $this->assertStringContainsString('<tPag>17</tPag>', $xml);
        $this->assertStringContainsString('<vPag>16.00</vPag>', $xml);
        $this->assertStringContainsString('<infRespTec>', $xml);
    }
}
