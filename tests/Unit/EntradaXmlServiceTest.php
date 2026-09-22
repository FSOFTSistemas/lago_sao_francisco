<?php

namespace Tests\Unit;

use App\Services\EntradaXmlService;
use PHPUnit\Framework\TestCase;

class EntradaXmlServiceTest extends TestCase
{
    public function test_faz_o_parser_correto_do_xml_da_nfe(): void
    {
        $service = new EntradaXmlService();

        $xml = <<<XML
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
            <cobr>
                <dup>
                    <nDup>001</nDup>
                    <dVenc>2026-10-22</dVenc>
                    <vDup>470.00</vDup>
                </dup>
            </cobr>
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

        $resultado = $service->parseXml($xml);

        $this->assertSame('35260112345678000195550010000012341000012345', $resultado['chave']);
        $this->assertSame('1234', $resultado['numero_nota']);
        $this->assertSame('1', $resultado['serie']);
        $this->assertSame('12345678000195', $resultado['fornecedor']['cnpj']);
        $this->assertSame('DISTRIBUIDORA DE BEBIDAS E ALIMENTOS LTDA', $resultado['fornecedor']['razao_social']);
        $this->assertSame(470.0, $resultado['totais']['valor_total']);
        $this->assertSame(450.0, $resultado['totais']['valor_produtos']);
        $this->assertSame(20.0, $resultado['totais']['valor_frete']);

        $this->assertCount(1, $resultado['itens']);
        $item = $resultado['itens'][0];
        $this->assertSame('BEB001', $item['codigo_fornecedor']);
        $this->assertSame('7891234567890', $item['codigo_barras']);
        $this->assertSame('REFRIGERANTE COCA COLA LATA 350ML', $item['descricao']);
        $this->assertSame(10.0, $item['quantidade']);
        $this->assertSame(45.0, $item['valor_unitario']);
        $this->assertSame(450.0, $item['valor_total']);
        $this->assertSame('00', $item['cst_icms']);
        $this->assertSame(81.0, $item['valor_icms']);

        $this->assertCount(1, $resultado['duplicatas']);
        $dup = $resultado['duplicatas'][0];
        $this->assertSame('001', $dup['numero']);
        $this->assertSame('2026-10-22', $dup['data_vencimento']);
        $this->assertSame(470.0, $dup['valor']);
    }
}
