<?php

namespace App\Services;

use App\Utils\FormatationUtil;
use App\Utils\ValidationEAN13Util;
use Illuminate\Support\Facades\File;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeService
{

    private $tools;

    public function __construct($config, $emitente)
    {
        $certificado = file_get_contents('../storage/app/public/certificados/' . $emitente->razao . '.pfx');
        $this->tools = new Tools(json_encode($config), Certificate::readPfx($certificado, $emitente->senhaCertificado));
    }

    public function gerarXml($venda, $emitente)
    {
        $nfe = new Make();
        $stdInNFe = new \stdClass();
        $stdInNFe->versao = '4.00';
        $stdInNFe->Id = null;
        $stdInNFe->pk_nItem = '';
        $infNFe = $nfe->taginfNFe($stdInNFe);

        $numeroNFe = $emitente->ultimaNFe + 1;
        $stdIde = new \stdClass();
        $stdIde->cUF = \App\Models\Empresa::getCUF($emitente->endereco->uf);
        $stdIde->cNF = rand(11111, 99999);
        $stdIde->natOp = $venda->cfopNota->natureza;

        $stdIde->mod = 55;
        $stdIde->serie = $emitente->serie;
        $stdIde->nNF = (int) $numeroNFe;
        $stdIde->dhEmi = date("Y-m-d\TH:i:sP");
        $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
        $stdIde->tpNF = $venda->tpNF;

        $stdIde->idDest = $emitente->endereco->uf != $venda->endereco_cliente->uf ? 2 : 1;
        $stdIde->cMunFG = $emitente->endereco->codigoIBGE;
        $stdIde->tpImp = 1;
        $stdIde->tpEmis = 1;
        $stdIde->cDV = 0;
        $stdIde->tpAmb = $emitente->ambiente;
        $stdIde->finNFe = $venda->finNF;
        $stdIde->indFinal = 1;
        $stdIde->indPres = 1;
        $stdIde->procEmi = '0';
        $stdIde->verProc = '3.10.31';
        $tagide = $nfe->tagide($stdIde);

        if ($venda->ref_nfe) {
            $stdrefNFe = new \stdClass();
            $stdrefNFe->refNFe = $venda->ref_nfe;
            $nfe->tagrefNFe($stdrefNFe);
        }

        //TAG EMITENTE
        $stdEmit = new \stdClass();
        $stdEmit->xNome = $emitente->razao;
        $stdEmit->xFant = $emitente->fantasia;

        $ie = str_replace(".", "", $emitente->rg_ie);
        $ie = str_replace("/", "", $ie);
        $ie = str_replace("-", "", $ie);
        $stdEmit->IE = $ie;

        $stdEmit->CRT = 1; // Simples nacional
        $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        if (strlen($cnpj) == 14) {
            $stdEmit->CNPJ = $cnpj;
        } else {
            $stdEmit->CPF = $cnpj;
        }
        $emit = $nfe->tagemit($stdEmit);
        // ENDERECO EMITENTE
        $stdEnderEmit = new \stdClass();
        $stdEnderEmit->xLgr = FormatationUtil::retiraAcentos($emitente->endereco->rua);
        $stdEnderEmit->nro = $emitente->endereco->numero;
        $stdEnderEmit->xCpl = FormatationUtil::retiraAcentos($emitente->endereco->complemento);

        $stdEnderEmit->xBairro = FormatationUtil::retiraAcentos($emitente->endereco->bairro);
        $stdEnderEmit->cMun = $emitente->endereco->codigoIBGE;
        $stdEnderEmit->xMun = FormatationUtil::retiraAcentos($emitente->endereco->cidade);
        $stdEnderEmit->UF = $emitente->endereco->uf;

        $telefone = $emitente->celular;
        $telefone = str_replace("(", "", $telefone);
        $telefone = str_replace(")", "", $telefone);
        $telefone = str_replace("-", "", $telefone);
        $telefone = str_replace(" ", "", $telefone);
        $stdEnderEmit->fone = $telefone;

        $cep = str_replace("-", "", $emitente->endereco->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderEmit->CEP = $cep;
        $stdEnderEmit->cPais = '1058';
        $stdEnderEmit->xPais = 'BRASIL';

        $enderEmit = $nfe->tagenderEmit($stdEnderEmit);

        // DESTINATARIO
        $stdDest = new \stdClass();
        $pFisica = false;
        $stdDest->xNome = FormatationUtil::retiraAcentos($venda->cliente->nome);

        if ($venda->cliente->contribuinte) {
            if ($venda->cliente->rg_ie == 'ISENTO') {
                $stdDest->indIEDest = "2";
            } else {
                $stdDest->indIEDest = "1";
            }
        } else {
            $stdDest->indIEDest = "9";
        }

        $cnpj_cpf = str_replace(".", "", $venda->cliente->cpf_cnpj);
        $cnpj_cpf = str_replace("/", "", $cnpj_cpf);
        $cnpj_cpf = str_replace("-", "", $cnpj_cpf);

        if (strlen($cnpj_cpf) == 14) {
            $stdDest->CNPJ = $cnpj_cpf;
            $ie = str_replace(".", "", $venda->cliente->rg_ie);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            $stdDest->IE = $ie;
        } else {
            $stdDest->CPF = $cnpj_cpf;
            $ie = str_replace(".", "", $venda->cliente->rg_ie);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            if (strtolower($ie) != "isento" && $venda->cliente->contribuinte) {
                $stdDest->IE = $ie;
            }
        }

        $dest = $nfe->tagdest($stdDest);

        //ENDEREÇO DESTINATÁRIO

        $stdEnderDest = new \stdClass();
        $stdEnderDest->xLgr = FormatationUtil::retiraAcentos($venda->endereco_cliente->rua);
        $stdEnderDest->nro = FormatationUtil::retiraAcentos($venda->endereco_cliente->numero);
        $stdEnderDest->xCpl = FormatationUtil::retiraAcentos($venda->endereco_cliente->complemento);
        $stdEnderDest->xBairro = FormatationUtil::retiraAcentos($venda->endereco_cliente->bairro);

        $telefone = $venda->cliente->celular;
        $telefone = str_replace("(", "", $telefone);
        $telefone = str_replace(")", "", $telefone);
        $telefone = str_replace("-", "", $telefone);
        $telefone = str_replace(" ", "", $telefone);
        $stdEnderDest->fone = $telefone;
        $stdEnderDest->cMun = FormatationUtil::retiraPontuacoes($venda->endereco_cliente->codigoIBGE);
        $stdEnderDest->xMun = FormatationUtil::retiraAcentos($venda->endereco_cliente->cidade);
        $stdEnderDest->UF = $venda->endereco_cliente->uf;

        $cep = str_replace("-", "", $venda->endereco_cliente->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderDest->CEP = $cep;
        $stdEnderDest->cPais = "1058";
        $stdEnderDest->xPais = "BRASIL";
        $enderDest = $nfe->tagenderDest($stdEnderDest);

        //ENTREGA
        $entrega = false;
        foreach ($venda->itens as $key => $i) {
            if ($i->produto->operVeic == 2) {
                $entrega = true;
            }
        }
        if ($entrega) {
            $stdEntrega = new \stdClass();
            if (strlen($cnpj_cpf) == 14) {
                $stdEntrega->CNPJ = $cnpj_cpf;
                $ie = str_replace(".", "", $venda->cliente->rg_ie);
                $ie = str_replace("/", "", $ie);
                $ie = str_replace("-", "", $ie);
                $stdEntrega->IE = $ie;
            } else {
                $stdEntrega->CPF = $cnpj_cpf;
                $ie = str_replace(".", "", $venda->cliente->rg_ie);
                $ie = str_replace("/", "", $ie);
                $ie = str_replace("-", "", $ie);
                if (strtolower($ie) != "isento" && $venda->cliente->contribuinte) {
                    $stdEntrega->IE = $ie;
                }
            }
            $stdEntrega->xNome = FormatationUtil::retiraAcentos($venda->cliente->nome);
            $stdEntrega->xLgr =  FormatationUtil::retiraAcentos($venda->endereco_cliente->rua);
            $stdEntrega->nro = FormatationUtil::retiraAcentos($venda->endereco_cliente->numero);
            $stdEntrega->xCpl = FormatationUtil::retiraAcentos($venda->endereco_cliente->complemento);
            $stdEntrega->xBairro = FormatationUtil::retiraAcentos($venda->endereco_cliente->bairro);
            $stdEntrega->cMun = FormatationUtil::retiraPontuacoes($venda->endereco_cliente->codigoIBGE);
            $stdEntrega->xMun = FormatationUtil::retiraAcentos($venda->endereco_cliente->cidade);
            $stdEntrega->UF = $venda->endereco_cliente->uf;
            $stdEntrega->CEP = $cep;
            $stdEntrega->cPais = '1058';
            $stdEntrega->xPais = 'BRASIL';
            $stdEntrega->fone = $telefone;

            $nfe->tagentrega($stdEntrega);
        }

        //ITENS DA NFE
        foreach ($venda->itens as $key => $i) {
            //TAG DE PRODUTO
            $stdProd = new \stdClass();
            $stdProd->item = $key + 1;

            $cod = ValidationEAN13Util::validate_EAN13Barcode($i->produto->codigo);

            $stdProd->cEAN = $cod ? $i->produto->codigo : 'SEM GTIN';
            $stdProd->cEANTrib = $cod ? $i->produto->codigo : 'SEM GTIN';
            $stdProd->cProd = $i->produto->id;
            $stdProd->xProd = FormatationUtil::retiraAcentos($i->produto->produto);

            $ncm = $i->produto->ncm;
            $ncm = str_replace(".", "", $ncm);
            $stdProd->NCM = $ncm;

            $stdProd->CFOP = $venda->cfopNota->cfop;

            $stdProd->uCom = $i->produto->un;
            $stdProd->qCom = $i->qtde;
            $stdProd->vUnCom = FormatationUtil::format($i->unitario);
            $stdProd->vProd = FormatationUtil::format(($i->qtde * $i->unitario));
            $stdProd->uTrib = $i->produto->un;
            $stdProd->qTrib = $i->qtde;
            $stdProd->vUnTrib = FormatationUtil::format($i->unitario);
            $stdProd->indTot = 1;
            if ($i->produto->tpProd == 1) {
                $stdVeicProd = new \stdClass();

                // Campos do veículo (adicionados)
                $stdVeicProd->item = $key + 1;
                $stdVeicProd->tpOp = $i->produto->operVeic;
                $stdVeicProd->chassi = $i->produto->chassiVeic;
                $stdVeicProd->cCor = $i->produto->cCorVeic;
                $stdVeicProd->xCor = $i->produto->corVeic;
                $stdVeicProd->pot = $i->produto->cvVeic;
                $stdVeicProd->cilin = $i->produto->cm3Veic;
                $stdVeicProd->pesoL = $i->produto->pesoLVeic;
                $stdVeicProd->pesoB = $i->produto->pesoBVeic;
                $stdVeicProd->nSerie = $i->produto->serieVeic;
                $stdVeicProd->tpComb = $i->produto->combVeic;
                $stdVeicProd->nMotor = $i->produto->nMotorVeic;
                $stdVeicProd->CMT = $i->produto->cargaVeic;
                $stdVeicProd->dist = $i->produto->distVeic;
                $stdVeicProd->anoMod = $i->produto->anoModVeic;
                $stdVeicProd->anoFab = $i->produto->anoFabVeic;
                $stdVeicProd->tpPint = $i->produto->tpPVeic;
                $stdVeicProd->tpVeic = $i->produto->tpVeic;
                $stdVeicProd->espVeic = $i->produto->espVeic;
                $stdVeicProd->VIN = $i->produto->vinVeic;
                $stdVeicProd->condVeic = $i->produto->condVeic;
                $stdVeicProd->cMod = $i->produto->cMarcaVeic;
                $stdVeicProd->cCorDENATRAN = $i->produto->cCorMontVeic;
                $stdVeicProd->lota = $i->produto->lotVeic;
                $stdVeicProd->tpRest = $i->produto->restriVeic;

                $nfe->tagveicProd($stdVeicProd);
            }
            $nfe->tagprod($stdProd);

            $stdImposto = new \stdClass();
            $stdImposto->item = $key + 1;
            $nfe->tagimposto($stdImposto);

            //ICMS
            $stdICMS = new \stdClass();
            $stdICMS->item = $key + 1;
            $stdICMS->orig = 0;
            $stdICMS->CSOSN = $i->produto->cst_csosn;
            $stdICMS->modBC = 0;
            $stdICMS->vBC = $stdProd->vProd;
            $stdICMS->pICMS = FormatationUtil::format($i->produto->icms);
            $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);
            $stdICMS->pCredSN = FormatationUtil::format($i->produto->icms);
            $stdICMS->vCredICMSSN = FormatationUtil::format($i->produto->icms);
            $ICMS = $nfe->tagICMSSN($stdICMS);

            //PIS
            $stdPIS = new \stdClass();
            $stdPIS->item = $key + 1;
            $stdPIS->CST = $i->produto->cst_pis;
            $stdPIS->vBC = FormatationUtil::format($i->produto->pis) > 0 ? $stdProd->vProd : 0.00;
            $stdPIS->pPIS = FormatationUtil::format($i->produto->pis);
            $stdPIS->vPIS = FormatationUtil::format(($stdProd->vProd) * ($i->produto->pis / 100));
            $PIS = $nfe->tagPIS($stdPIS);

            //COFINS
            $stdCOFINS = new \stdClass();
            $stdCOFINS->item = $key + 1;
            $stdCOFINS->CST = $i->produto->cst_cofins;
            $stdCOFINS->vBC = FormatationUtil::format($i->produto->cofins) > 0 ? $stdProd->vProd : 0.00;
            $stdCOFINS->pCOFINS = FormatationUtil::format($i->produto->cofins);
            $stdCOFINS->vCOFINS = FormatationUtil::format(($stdProd->vProd) *
                ($i->produto->cofins / 100));
            $COFINS = $nfe->tagCOFINS($stdCOFINS);

            //IPI
            $std = new \stdClass();
            $std->item = $key + 1;
            $std->cEnq = '999';
            $std->CST = $i->produto->ipi;
            $std->vBC = FormatationUtil::format($i->produto->ipi) > 0 ? $stdProd->vProd : 0.00;
            $std->pIPI = FormatationUtil::format($i->produto->ipi);
            $std->vIPI = $stdProd->vProd * FormatationUtil::format(($i->produto->ipi / 100));
            $nfe->tagIPI($std);
        }

        $stdTransp = new \stdClass();
        $stdTransp->modFrete = '9';

        $transp = $nfe->tagtransp($stdTransp);

        //TOTALIZADOR NFE
        $stdICMSTot = new \stdClass();
        $stdICMSTot->vProd = 0.00;
        $stdICMSTot->vBC = 0.00;
        $stdICMSTot->vICMS = 0.00;
        $stdICMSTot->vICMSDeson = 0.00;
        $stdICMSTot->vBCST = 0.00;
        $stdICMSTot->vST = 0.00;
        $stdICMSTot->vFrete = 0.00;
        $stdICMSTot->vSeg = 0.00;
        $stdICMSTot->vDesc = FormatationUtil::format($venda->desconto);
        $stdICMSTot->vII = 0.00;
        $stdICMSTot->vIPI = 0.00;
        $stdICMSTot->vPIS = 0.00;
        $stdICMSTot->vCOFINS = 0.00;
        $stdICMSTot->vOutro = 0.00;
        $stdICMSTot->vTotTrib = 0.00;
        $stdICMSTot->vNF = FormatationUtil::format($venda->total);

        //DUPLICATAS
        $stdFat = new \stdClass();
        $stdFat->nFat = (int) $numeroNFe;
        $stdFat->vOrig = FormatationUtil::format($venda->subtotal);
        $stdFat->vDesc = FormatationUtil::format($venda->desconto);
        $stdFat->vLiq = FormatationUtil::format($venda->total);
        if ($venda->tipo_pagamento != '90') {
            $fatura = $nfe->tagfat($stdFat);
        }

        foreach ($venda->fatura as $key => $fat) {
            // $stdDup = new \stdClass();
            // $stdDup->nDup = '00' . ($key + 1);
            // $stdDup->dVenc = $fat->vencimento;
            // $stdDup->dVenc = date('Y-m-d');
            // $stdDup->vDup = FormatationUtil::format($fat->valor);

            // $nfe->tagdup($stdDup);

            $stdPag = new \stdClass();
            $pag = $nfe->tagpag($stdPag);

            $stdDetPag = new \stdClass();
            switch ($fat->forma_pag->descricao) {
                case "Dinheiro":
                    $stdDetPag->tPag = '01';
                    break;
                case "Cheque":
                    $stdDetPag->tPag = '02';
                    break;
                case "Cartão de Crédito":
                    $stdDetPag->tPag = '03';
                    break;
                case "Cartão de Débito":
                    $stdDetPag->tPag = '04';
                    break;
                case "Crédito Loja":
                    $stdDetPag->tPag = '05';
                    break;
                case "Vale Alimentação":
                    $stdDetPag->tPag = '10';
                    break;
                case "Vale Refeição":
                    $stdDetPag->tPag = '11';
                    break;
                case "Vale Presente":
                    $stdDetPag->tPag = '12';
                    break;
                case "Vale Combustível":
                    $stdDetPag->tPag = '13';
                    break;
                case "Duplicata Mercantil":
                    $stdDetPag->tPag = '14';
                    break;
                case "Boleto Bancário":
                    $stdDetPag->tPag = '15';
                    break;
                case "Depósito Bancário":
                    $stdDetPag->tPag = '16';
                    break;
                case "PIX":
                    $stdDetPag->tPag = '17';
                    break;
                case "Sem Pagamento":
                    $stdDetPag->tPag = '90';
                    break;
                case "Outros":
                    $stdDetPag->tPag = '99';
                    break;
                default:
                    break;
            }
            $stdDetPag->vPag = $fat->forma_pag->descricao != 'Sem Pagamento' ? FormatationUtil::format($fat->valor) : 0;
            $stdDetPag->indPag = 1;
            $stdDetPag->vTroco = 0;
            if ($fat->forma_pag->descricao == 'Cartão de Crédito' || $fat->forma_pag->descricao == 'Cartão de Débito') {
                $stdDetPag->tpIntegra = '2';
            }
            $detPag = $nfe->tagdetPag($stdDetPag);
        }


        $stdInfCpl = new \stdClass();
        $stdInfCpl->infCpl = $venda->info_complementares;
        $infCpl = $nfe->taginfAdic($stdInfCpl);

        //TAG AUTORIZADOR XML VARIAVEL NO ARQUIVO .ENV, ESTADO DA BAHIA OBRIGATORIO
        if (getenv('AUT_XML') != '') {
            $std = new \stdClass();
            $cnpj = getenv('AUT_XML');
            $cnpj = str_replace(".", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);
            $std->CNPJ = $cnpj;
            $aut = $nfe->tagautXML($std);
        }

        //TAG RESPONSAVEL TECNICO
        $std = new \stdClass();
        $std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
        $std->xContato = getenv('RESP_NOME'); //Nome da pessoa a ser contatada
        $std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
        $std->fone = getenv('RESP_FONE');
        $nfe->taginfRespTec($std);

        try {
            $nfe->montaNFe();
            $arr = [
                'chave' => $nfe->getChave(),
                'xml' => $nfe->getXML(),
                'nNf' => $stdIde->nNF,
            ];
            return $arr;
        } catch (\Exception $e) {
            return [
                'erros_xml' => $nfe->getErrors(),
            ];
        }
    }

    public function sign($xml)
    {
        return $this->tools->signNFe($xml);
    }

    public function transmitir($signXml, $chave, $caminho)
    {
        try {
            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            $resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

            $st = new Standardize();
            $std = $st->toStd($resp);
            sleep(2);
            if ($std->cStat != 103) {

                return [
                    'erro' => "[$std->cStat] - $std->xMotivo",
                ];
            }
            $recibo = $std->infRec->nRec;
            $protocolo = $this->tools->sefazConsultaRecibo($recibo);
            sleep(3);
            $xml = Complements::toAuthorize($signXml, $protocolo);
            if (!File::exists(public_path($caminho . '/'))) {
                File::makeDirectory(public_path($caminho . '/'), 0777, true, true);
            }
            file_put_contents(public_path($caminho . '/') . $chave . '.xml', $xml);
            return [
                'sucesso' => $recibo,
            ];
        } catch (\Exception $e) {
            return [
                'erro' => $e->getMessage(),
            ];
        }
    }

    public function inutilizarNum($serie, $numI, $numF, $xJust, $caminho)
    {
        try {
            $response = $this->tools->sefazInutiliza($serie, $numI, $numF, $xJust);
            sleep(2);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();

            if ($std->infInut->cStat == 102 || $std->infInut->cStat == 563) {
                $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                if (!File::exists(public_path($caminho . '/'))) {
                    File::makeDirectory(public_path($caminho . '/'), 0777, true, true);
                }
                file_put_contents(public_path($caminho . '/') . $std->infInut->attributes->Id . '.xml', $xml);

                return $json;
            } else {
                ['erro' => true, 'data' => $arr];
            }
        } catch (\Exception $e) {
            return ['erro' => true, 'data' => $e->getMessage()];
        }
    }

    public function cartaCorrecao($venda, $justificativa, $caminho)
    {
        try {
            $chave = $venda->chave;
            $xCorrecao = $justificativa;
            $nSeqEvento = $venda->sequencia_evento + 1;
            $response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
            sleep(2);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();
            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '135' || $cStat == '136') {
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    if (!File::exists(public_path($caminho . '/'))) {
                        File::makeDirectory(public_path($caminho . '/'), 0777, true, true);
                    }
                    file_put_contents(public_path($caminho . '/') . $chave . '.xml', $xml);

                    $venda->sequencia_evento += 1;
                    $venda->save();
                    return $json;
                } else {
                    return ['erro' => true, 'data' => $arr];
                }
            }
        } catch (\Exception $e) {
            return ['erro' => true, 'data' => $e->getMessage()];
        }
    }

    public function cancelar($venda, $justificativa, $caminho)
    {
        try {
            $chave = $venda->chave;
            $response = $this->tools->sefazConsultaChave($chave);
            sleep(2);
            $stdCl = new Standardize($response);
            $arr = $stdCl->toArray();
            $xJust = $justificativa;
            $nProt = $arr['protNFe']['infProt']['nProt'];

            $response = $this->tools->sefazCancela($chave, $xJust, $nProt);
            sleep(2);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();
            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    if (!File::exists(public_path($caminho . '/'))) {
                        File::makeDirectory(public_path($caminho . '/'), 0777, true, true);
                    }
                    file_put_contents(public_path($caminho . '/') . $chave . '.xml', $xml);

                    return $json;
                } else {
                    return ['erro' => true, 'data' => $arr];
                }
            }
        } catch (\Exception $e) {
            return ['erro' => true, 'data' => $e->getMessage()];
        }
    }
}
