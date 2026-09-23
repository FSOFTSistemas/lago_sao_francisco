<?php

namespace Tests\Unit;

use App\Services\DanfeService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DanfeServiceTest extends TestCase
{
    protected function getXmlValido(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<nfeProc xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
    <NFe>
        <infNFe Id="NFe35260112345678000195550010000012341000012345" versao="4.00">
            <ide>
                <cUF>35</cUF>
                <cNF>00001234</cNF>
                <natOp>VENDA DE MERCADORIA</natOp>
                <mod>55</mod>
                <serie>1</serie>
                <nNF>1234</nNF>
                <dhEmi>2026-09-22T10:00:00-03:00</dhEmi>
                <dhSaiEnt>2026-09-22T10:00:00-03:00</dhSaiEnt>
                <tpNF>1</tpNF>
                <idDest>1</idDest>
                <cMunFG>3550308</cMunFG>
                <tpImp>1</tpImp>
                <tpEmis>1</tpEmis>
                <cDV>5</cDV>
                <tpAmb>1</tpAmb>
                <finNFe>1</finNFe>
                <indFinal>0</indFinal>
                <indPres>1</indPres>
                <procEmi>0</procEmi>
                <verProc>4.00</verProc>
            </ide>
            <emit>
                <CNPJ>12345678000195</CNPJ>
                <xNome>DISTRIBUIDORA DE BEBIDAS E ALIMENTOS LTDA</xNome>
                <xFant>DISTRIBUIDORA MODELO</xFant>
                <enderEmit>
                    <xLgr>Av Central</xLgr>
                    <nro>1000</nro>
                    <xBairro>Centro</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>São Paulo</xMun>
                    <UF>SP</UF>
                    <CEP>01000000</CEP>
                </enderEmit>
                <IE>123456789</IE>
                <CRT>3</CRT>
            </emit>
            <dest>
                <CNPJ>99999999000199</CNPJ>
                <xNome>HOTEL LAGO SAO FRANCISCO LTDA</xNome>
                <enderDest>
                    <xLgr>Rodovia PE 095</xLgr>
                    <nro>KM 10</nro>
                    <xBairro>Zona Rural</xBairro>
                    <cMun>2604106</cMun>
                    <xMun>Caruaru</xMun>
                    <UF>PE</UF>
                    <CEP>55000000</CEP>
                </enderDest>
                <indIEDest>1</indIEDest>
                <IE>012345678</IE>
            </dest>
            <det nItem="1">
                <prod>
                    <cProd>BEB001</cProd>
                    <cEAN>7891234567890</cEAN>
                    <xProd>REFRIGERANTE COCA COLA LATA 350ML</xProd>
                    <NCM>22021000</NCM>
                    <CEST>0300100</CEST>
                    <CFOP>5102</CFOP>
                    <uCom>FD</uCom>
                    <qCom>10.0000</qCom>
                    <vUnCom>45.000000</vUnCom>
                    <vProd>450.00</vProd>
                    <cEANTrib>7891234567890</cEANTrib>
                    <uTrib>FD</uTrib>
                    <qTrib>10.0000</qTrib>
                    <vUnTrib>45.000000</vUnTrib>
                    <indTot>1</indTot>
                </prod>
                <imposto>
                    <ICMS>
                        <ICMS00>
                            <orig>0</orig>
                            <CST>00</CST>
                            <modBC>3</modBC>
                            <vBC>450.00</vBC>
                            <pICMS>18.0000</pICMS>
                            <vICMS>81.00</vICMS>
                        </ICMS00>
                    </ICMS>
                    <PIS>
                        <PISAliq>
                            <CST>01</CST>
                            <vBC>450.00</vBC>
                            <pPIS>1.6500</pPIS>
                            <vPIS>7.43</vPIS>
                        </PISAliq>
                    </PIS>
                    <COFINS>
                        <COFINSAliq>
                            <CST>01</CST>
                            <vBC>450.00</vBC>
                            <pCOFINS>7.6000</pCOFINS>
                            <vCOFINS>34.20</vCOFINS>
                        </COFINSAliq>
                    </COFINS>
                </imposto>
            </det>
            <total>
                <ICMSTot>
                    <vBC>450.00</vBC>
                    <vICMS>81.00</vICMS>
                    <vICMSDeson>0.00</vICMSDeson>
                    <vFCP>0.00</vFCP>
                    <vBCST>0.00</vBCST>
                    <vST>0.00</vST>
                    <vFCPST>0.00</vFCPST>
                    <vFCPSTRet>0.00</vFCPSTRet>
                    <vProd>450.00</vProd>
                    <vFrete>20.00</vFrete>
                    <vSeg>0.00</vSeg>
                    <vDesc>0.00</vDesc>
                    <vII>0.00</vII>
                    <vIPI>0.00</vIPI>
                    <vIPIDevol>0.00</vIPIDevol>
                    <vPIS>7.43</vPIS>
                    <vCOFINS>34.20</vCOFINS>
                    <vOutro>0.00</vOutro>
                    <vNF>470.00</vNF>
                </ICMSTot>
            </total>
            <transp>
                <modFrete>0</modFrete>
            </transp>
            <cobr>
                <dup>
                    <nDup>001</nDup>
                    <dVenc>2026-10-22</dVenc>
                    <vDup>470.00</vDup>
                </dup>
            </cobr>
            <pag>
                <detPag>
                    <tPag>15</tPag>
                    <vPag>470.00</vPag>
                </detPag>
            </pag>
        </infNFe>
    </NFe>
    <protNFe versao="4.00">
        <infProt>
            <tpAmb>1</tpAmb>
            <chNFe>35260112345678000195550010000012341000012345</chNFe>
            <dhRecbto>2026-09-22T10:05:00-03:00</dhRecbto>
            <nProt>135260000001234</nProt>
            <cStat>100</cStat>
            <xMotivo>Autorizado o uso da NF-e</xMotivo>
        </infProt>
    </protNFe>
</nfeProc>
XML;
    }

    public function test_gera_danfe_pdf_com_sucesso_a_partir_de_xml_valido(): void
    {
        $service = new DanfeService();
        $xml = $this->getXmlValido();

        $pdfBytes = $service->gerarPdf($xml);

        $this->assertNotEmpty($pdfBytes);
        // Garante que os primeiros bytes do documento são a assinatura mágica de PDF (%PDF-)
        $this->assertStringStartsWith('%PDF-', $pdfBytes);
        // Garante que o documento gerado tem conteúdo suficiente para um A4 renderizado
        $this->assertGreaterThan(1000, strlen($pdfBytes));
    }

    public function test_gera_danfe_com_marca_d_agua_de_cancelamento(): void
    {
        $service = new DanfeService();
        $xml = $this->getXmlValido();

        $pdfBytes = $service->gerarPdf($xml, null, true);

        $this->assertNotEmpty($pdfBytes);
        $this->assertStringStartsWith('%PDF-', $pdfBytes);
        $this->assertGreaterThan(1000, strlen($pdfBytes));
    }

    public function test_lanca_excecao_se_xml_estiver_vazio(): void
    {
        $service = new DanfeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O conteúdo do XML não pode ser vazio para a geração do DANFE.');

        $service->gerarPdf('   ');
    }

    public function test_lanca_excecao_se_xml_nao_contiver_estrutura_de_nfe(): void
    {
        $service = new DanfeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O documento informado não possui a estrutura básica de uma NF-e (<infNFe> ou <NFe>).');

        $service->gerarPdf('<documentoGenerico><id>123</id></documentoGenerico>');
    }

    public function test_lanca_excecao_se_xml_for_sintaticamente_invalido(): void
    {
        $service = new DanfeService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Falha na estrutura sintática do XML da NF-e');

        $service->gerarPdf('<NFe><infNFe>Tag nao fechada');
    }
}
