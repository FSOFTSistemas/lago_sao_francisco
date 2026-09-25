<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\NotaFiscal;
use App\Utils\FormatationUtil;
use App\Utils\ValidationEAN13Util;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;

class NFeService
{
    private ?Tools $tools = null;

    public function __construct(?Tools $tools = null)
    {
        $this->tools = $tools;
    }

    /**
     * Define ou atualiza a instância de Tools.
     */
    public function setTools(Tools $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function getTools(): ?Tools
    {
        return $this->tools;
    }

    /**
     * Obtém ou inicializa a instância de Tools para a empresa informada.
     */
    public function obterTools(Empresa $empresa): Tools
    {
        if ($this->tools) {
            return $this->tools;
        }

        $dfeService = app(DfeService::class);
        $this->tools = $dfeService->inicializarTools($empresa);
        return $this->tools;
    }

    /**
     * Constrói o XML da NF-e (modelo 55 layout 4.00) utilizando a classe Make do SPED-NFe.
     *
     * @param array|NotaFiscal $dados
     * @param Empresa|null $empresa
     * @return array
     */
    public function gerarXml(array|NotaFiscal $dados, ?Empresa $empresa = null): array
    {
        $nfe = new Make();

        // 1. Resolver a Empresa emitente
        $empresa = $this->resolverEmpresa($dados, $empresa);
        if (!$empresa) {
            return [
                'sucesso' => false,
                'erros'   => ['Empresa emitente não informada ou não encontrada.'],
            ];
        }

        $preferencia = $empresa->preferencia;
        $enderecoEmit = $empresa->endereco ?? $this->obterEnderecoPadrao();

        // 2. TAG infNFe
        $stdInNFe = new \stdClass();
        $stdInNFe->versao = '4.00';
        $stdInNFe->Id = null;
        $stdInNFe->pk_nItem = null;
        $nfe->taginfNFe($stdInNFe);

        // 3. TAG ide (Identificação da NF-e)
        $numeroNFe = (int) ($dados['numero'] ?? $dados->numero ?? (($preferencia->numero_ultima_nota ?? 0) + 1));
        $serieNFe  = (int) ($dados['serie'] ?? $dados->serie ?? ($preferencia->serie ?? 1));
        $ufEmit    = strtoupper(trim($enderecoEmit->uf ?? 'PE'));
        $cUFEmit   = Empresa::getCUF($ufEmit);

        $tipoNotaStr = strtolower((string) ($dados['tipo_nota'] ?? $dados->tp_nota ?? '1'));
        $tpNF = in_array($tipoNotaStr, ['0', 'entrada']) ? 0 : 1;

        $clienteObj = $this->resolverCliente($dados);
        $enderecoDest = $clienteObj ? ($clienteObj->endereco ?? null) : null;
        $ufDest = strtoupper(trim($enderecoDest->uf ?? ($dados['uf_cliente'] ?? $ufEmit)));
        $idDest = ($ufDest !== $ufEmit) ? 2 : 1;

        $dhEmi = !empty($dados['data_emissao']) ? date("Y-m-d\TH:i:sP", strtotime($dados['data_emissao'])) : date("Y-m-d\TH:i:sP");
        $dhSaiEnt = !empty($dados['data_saida']) ? date("Y-m-d\TH:i:sP", strtotime($dados['data_saida'])) : $dhEmi;

        $ambiente = (int) ($preferencia->ambiente_dfe ?? 2); // 1 = Produção, 2 = Homologação

        $stdIde = new \stdClass();
        $stdIde->cUF = (int) $cUFEmit;
        $stdIde->cNF = str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $stdIde->natOp = substr(FormatationUtil::retiraAcentos((string) ($dados['natureza_operacao'] ?? $dados['natOp'] ?? 'VENDA DE MERCADORIA')), 0, 60);
        $stdIde->mod = 55;
        $stdIde->serie = $serieNFe;
        $stdIde->nNF = $numeroNFe;
        $stdIde->dhEmi = $dhEmi;
        $stdIde->dhSaiEnt = $dhSaiEnt;
        $stdIde->tpNF = $tpNF;
        $stdIde->idDest = $idDest;
        $stdIde->cMunFG = FormatationUtil::retiraPontuacoes((string) ($enderecoEmit->ibge ?? '2606002'));
        $stdIde->tpImp = 1; // 1 = Retrato
        $stdIde->tpEmis = 1; // 1 = Normal
        $stdIde->cDV = null; // Calculado pelo Make
        $stdIde->tpAmb = $ambiente;
        $stdIde->finNFe = (int) ($dados['finalidade'] ?? 1); // 1 = Normal, 2 = Complementar, 3 = Ajuste, 4 = Devolução
        $stdIde->indFinal = 1; // 1 = Consumidor final
        $stdIde->indPres = 1;  // 1 = Operação presencial
        $stdIde->procEmi = '0';
        $stdIde->verProc = '1.0';
        $nfe->tagide($stdIde);

        // Se houver chave referenciada
        $refNfe = $dados['nfe_referenciada'] ?? $dados['ref_nfe'] ?? ($dados->nfe_referenciavel ?? null);
        if ($refNfe) {
            $stdRef = new \stdClass();
            $stdRef->refNFe = preg_replace('/\D/', '', $refNfe);
            $nfe->tagrefNFe($stdRef);
        }

        // 4. TAG emit (Emitente)
        $stdEmit = new \stdClass();
        $stdEmit->xNome = FormatationUtil::retiraAcentos(substr($empresa->razao_social, 0, 60));
        $stdEmit->xFant = FormatationUtil::retiraAcentos(substr($empresa->nome_fantasia ?: $empresa->razao_social, 0, 60));
        $stdEmit->IE = FormatationUtil::retiraPontuacoes($empresa->inscricao_estadual);

        $regime = strtolower((string) ($preferencia->regime_tributario ?? 'simples'));
        $crt = (str_contains($regime, 'simples') || $preferencia->regime_tributario == 1) ? 1 : 3;
        $stdEmit->CRT = $crt;

        $cnpjEmit = preg_replace('/\D/', '', (string) $empresa->cnpj);
        if (strlen($cnpjEmit) === 14) {
            $stdEmit->CNPJ = $cnpjEmit;
        } else {
            $stdEmit->CPF = $cnpjEmit;
        }
        $nfe->tagemit($stdEmit);

        // 5. TAG enderEmit (Endereço do Emitente)
        $stdEnderEmit = new \stdClass();
        $stdEnderEmit->xLgr = FormatationUtil::retiraAcentos($enderecoEmit->logradouro ?? 'Rua');
        $stdEnderEmit->nro = $enderecoEmit->numero ?? 'S/N';
        $stdEnderEmit->xCpl = !empty($enderecoEmit->complemento) ? FormatationUtil::retiraAcentos($enderecoEmit->complemento) : null;
        $stdEnderEmit->xBairro = FormatationUtil::retiraAcentos($enderecoEmit->bairro ?? 'Centro');
        $stdEnderEmit->cMun = FormatationUtil::retiraPontuacoes((string) ($enderecoEmit->ibge ?? '2606002'));
        $stdEnderEmit->xMun = FormatationUtil::retiraAcentos($enderecoEmit->cidade ?? 'Garanhuns');
        $stdEnderEmit->UF = $ufEmit;
        $stdEnderEmit->CEP = FormatationUtil::retiraPontuacoes((string) ($enderecoEmit->cep ?? '55299560'));
        $stdEnderEmit->cPais = '1058';
        $stdEnderEmit->xPais = 'BRASIL';
        if (!empty($empresa->telefone)) {
            $stdEnderEmit->fone = FormatationUtil::retiraPontuacoes($empresa->telefone);
        }
        $nfe->tagenderEmit($stdEnderEmit);

        // 6. TAG dest (Destinatário)
        $stdDest = new \stdClass();
        $nomeDest = $clienteObj ? ($clienteObj->nome_razao_social ?? $clienteObj->nome ?? 'CONSUMIDOR') : ($dados['cliente']['razao_social'] ?? 'CONSUMIDOR FINAL');
        $stdDest->xNome = FormatationUtil::retiraAcentos(substr($nomeDest, 0, 60));

        $docDest = preg_replace('/\D/', '', (string) ($clienteObj->cpf_cnpj ?? $dados['cliente']['cpf_cnpj'] ?? $dados['cpf_cnpj'] ?? ''));
        $ieDest  = preg_replace('/\D/', '', (string) ($clienteObj->rg_ie ?? $dados['cliente']['rg_ie'] ?? ''));

        if (strlen($docDest) === 14) {
            $stdDest->CNPJ = $docDest;
            if (!empty($ieDest) && strtoupper($ieDest) !== 'ISENTO') {
                $stdDest->indIEDest = '1'; // Contribuinte
                $stdDest->IE = $ieDest;
            } elseif (strtoupper((string) ($clienteObj->rg_ie ?? '')) === 'ISENTO') {
                $stdDest->indIEDest = '2'; // Isento
            } else {
                $stdDest->indIEDest = '9'; // Não contribuinte
            }
        } elseif (strlen($docDest) === 11) {
            $stdDest->CPF = $docDest;
            $stdDest->indIEDest = '9'; // Pessoa Física - Não Contribuinte
        } else {
            // Consumidor sem documento identificado
            $stdDest->indIEDest = '9';
        }
        $nfe->tagdest($stdDest);

        // 7. TAG enderDest (Endereço do Destinatário)
        if ($enderecoDest || strlen($docDest) > 0) {
            $stdEnderDest = new \stdClass();
            $stdEnderDest->xLgr = FormatationUtil::retiraAcentos($enderecoDest->logradouro ?? $enderecoEmit->logradouro ?? 'Rua');
            $stdEnderDest->nro = $enderecoDest->numero ?? 'S/N';
            $stdEnderDest->xCpl = !empty($enderecoDest->complemento) ? FormatationUtil::retiraAcentos($enderecoDest->complemento) : null;
            $stdEnderDest->xBairro = FormatationUtil::retiraAcentos($enderecoDest->bairro ?? $enderecoEmit->bairro ?? 'Centro');
            $stdEnderDest->cMun = FormatationUtil::retiraPontuacoes((string) ($enderecoDest->ibge ?? $enderecoEmit->ibge ?? '2606002'));
            $stdEnderDest->xMun = FormatationUtil::retiraAcentos($enderecoDest->cidade ?? $enderecoEmit->cidade ?? 'Garanhuns');
            $stdEnderDest->UF = strtoupper(trim((string) ($enderecoDest->uf ?? $ufEmit)));
            $stdEnderDest->CEP = FormatationUtil::retiraPontuacoes((string) ($enderecoDest->cep ?? $enderecoEmit->cep ?? '55299560'));
            $stdEnderDest->cPais = '1058';
            $stdEnderDest->xPais = 'BRASIL';
            if ($clienteObj && !empty($clienteObj->telefone)) {
                $stdEnderDest->fone = FormatationUtil::retiraPontuacoes($clienteObj->telefone);
            }
            $nfe->tagenderDest($stdEnderDest);
        }

        // 8. ITENS DA NOTA (tagprod, tagimposto, ICMS, PIS, COFINS)
        $itens = $this->resolverItens($dados);
        $totalProdutos = 0.00;
        $totalDescontos = 0.00;

        foreach ($itens as $key => $item) {
            $nItem = $key + 1;

            $stdProd = new \stdClass();
            $stdProd->item = $nItem;
            $stdProd->cProd = (string) ($item['produto_id'] ?? $item['id'] ?? $nItem);

            $ean = $item['ean'] ?? '';
            $eanValido = (!empty($ean) && ValidationEAN13Util::validate_EAN13Barcode($ean));
            $stdProd->cEAN = $eanValido ? $ean : 'SEM GTIN';
            $stdProd->cEANTrib = $eanValido ? $ean : 'SEM GTIN';

            $descProd = $item['produto'] ?? $item['descricao'] ?? 'PRODUTO';
            $stdProd->xProd = FormatationUtil::retiraAcentos(substr($descProd, 0, 120));

            $ncm = preg_replace('/\D/', '', (string) ($item['ncm'] ?? '21069090'));
            $stdProd->NCM = strlen($ncm) >= 2 ? str_pad($ncm, 8, '0', STR_PAD_RIGHT) : '21069090';

            $stdProd->CFOP = preg_replace('/\D/', '', (string) ($item['cfop'] ?? ($preferencia->cfop_padrao ?? '5102')));
            $stdProd->uCom = strtoupper(substr(trim((string) ($item['un'] ?? $item['unidade'] ?? 'UN')), 0, 6));
            $stdProd->qCom = FormatationUtil::format((float) ($item['quantidade'] ?? 1), 4);

            $vUnitario = (float) ($item['valor_unitario'] ?? $item['v_unitario'] ?? 0);
            $stdProd->vUnCom = FormatationUtil::format($vUnitario, 4);

            $vProd = round(((float) ($item['quantidade'] ?? 1)) * $vUnitario, 2);
            $stdProd->vProd = FormatationUtil::format($vProd, 2);

            $stdProd->uTrib = $stdProd->uCom;
            $stdProd->qTrib = $stdProd->qCom;
            $stdProd->vUnTrib = $stdProd->vUnCom;

            $vDescItem = (float) ($item['desconto'] ?? 0);
            if ($vDescItem > 0) {
                $stdProd->vDesc = FormatationUtil::format($vDescItem, 2);
                $totalDescontos += $vDescItem;
            }

            $stdProd->indTot = 1;
            $nfe->tagprod($stdProd);
            $totalProdutos += $vProd;

            // Bloco de Impostos do Item
            $stdImposto = new \stdClass();
            $stdImposto->item = $nItem;
            $nfe->tagimposto($stdImposto);

            // ICMS
            if ($crt === 1) {
                // Simples Nacional
                $stdICMS = new \stdClass();
                $stdICMS->item = $nItem;
                $stdICMS->orig = 0; // Nacional
                $csosn = (string) ($item['csosn'] ?? '102');
                $stdICMS->CSOSN = $csosn;

                if (in_array($csosn, ['101', '201'])) {
                    $pCred = (float) ($item['aliquota'] ?? 0);
                    $stdICMS->pCredSN = FormatationUtil::format($pCred, 2);
                    $stdICMS->vCredICMSSN = FormatationUtil::format($vProd * ($pCred / 100), 2);
                }
                $nfe->tagICMSSN($stdICMS);
            } else {
                // Regime Normal (CRT 3)
                $stdICMS = new \stdClass();
                $stdICMS->item = $nItem;
                $stdICMS->orig = 0;
                $stdICMS->CST = (string) ($item['cst'] ?? '00');
                $stdICMS->modBC = 0;
                $stdICMS->vBC = FormatationUtil::format($vProd, 2);
                $pICMS = (float) ($item['aliquota'] ?? 0);
                $stdICMS->pICMS = FormatationUtil::format($pICMS, 2);
                $stdICMS->vICMS = FormatationUtil::format($vProd * ($pICMS / 100), 2);
                $nfe->tagICMS($stdICMS);
            }

            // PIS (CST 99 Outras Operações com valor zero para Simples Nacional)
            $stdPIS = new \stdClass();
            $stdPIS->item = $nItem;
            $stdPIS->CST = ($crt === 1) ? '99' : (string) ($item['cst_pis'] ?? '07');
            $stdPIS->vBC = '0.00';
            $stdPIS->pPIS = '0.00';
            $stdPIS->vPIS = '0.00';
            $nfe->tagPIS($stdPIS);

            // COFINS (CST 99 Outras Operações com valor zero para Simples Nacional)
            $stdCOFINS = new \stdClass();
            $stdCOFINS->item = $nItem;
            $stdCOFINS->CST = ($crt === 1) ? '99' : (string) ($item['cst_cofins'] ?? '07');
            $stdCOFINS->vBC = '0.00';
            $stdCOFINS->pCOFINS = '0.00';
            $stdCOFINS->vCOFINS = '0.00';
            $nfe->tagCOFINS($stdCOFINS);
        }

        // 9. TAG transp (Transporte)
        $stdTransp = new \stdClass();
        $stdTransp->modFrete = 9; // 9 = Sem Ocorrência de Transporte
        $nfe->tagtransp($stdTransp);

        // 10. TAG ICMSTot (Totalizadores da NF-e)
        $totalNota = max(0, $totalProdutos - $totalDescontos);

        $stdTot = new \stdClass();
        $stdTot->vBC = '0.00';
        $stdTot->vICMS = '0.00';
        $stdTot->vICMSDeson = '0.00';
        $stdTot->vFCP = '0.00';
        $stdTot->vBCST = '0.00';
        $stdTot->vST = '0.00';
        $stdTot->vFCPST = '0.00';
        $stdTot->vFCPSTRet = '0.00';
        $stdTot->vProd = FormatationUtil::format($totalProdutos, 2);
        $stdTot->vFrete = '0.00';
        $stdTot->vSeg = '0.00';
        $stdTot->vDesc = FormatationUtil::format($totalDescontos, 2);
        $stdTot->vII = '0.00';
        $stdTot->vIPI = '0.00';
        $stdTot->vIPIDevol = '0.00';
        $stdTot->vPIS = '0.00';
        $stdTot->vCOFINS = '0.00';
        $stdTot->vOutro = '0.00';
        $stdTot->vNF = FormatationUtil::format($totalNota, 2);
        $stdTot->vTotTrib = '0.00';
        $nfe->tagICMSTot($stdTot);

        // 11. TAG pag & detPag (Formas de Pagamento)
        $stdPag = new \stdClass();
        $stdPag->vTroco = '0.00';
        $nfe->tagpag($stdPag);

        $formaPagamento = $dados['forma_pagamento_detalhada'] ?? $dados['forma_pagamento'] ?? '01';
        $stdDetPag = new \stdClass();
        $stdDetPag->indPag = 0; // 0 = Pagamento à Vista

        // Mapeamento de Códigos de Pagamento SEFAZ
        $codigoPag = match ((string) $formaPagamento) {
            'Dinheiro', '01'            => '01',
            'Cheque', '02'              => '02',
            'Cartão de Crédito', '03'   => '03',
            'Cartão de Débito', '04'    => '04',
            'Crédito Loja', '05'        => '05',
            'Vale Alimentação', '10'    => '10',
            'Vale Refeição', '11'       => '11',
            'Boleto Bancário', '15'     => '15',
            'Depósito Bancário', '16'   => '16',
            'PIX', '17'                 => '17',
            'Sem Pagamento', '90'       => '90',
            default                     => '01',
        };

        $stdDetPag->tPag = $codigoPag;
        $stdDetPag->vPag = ($codigoPag === '90') ? '0.00' : FormatationUtil::format($totalNota, 2);

        // Se for cartão, incluir tpIntegra = 2 (POS avulso)
        if (in_array($codigoPag, ['03', '04'])) {
            $stdDetPag->tpIntegra = 2;
        }
        $nfe->tagdetPag($stdDetPag);

        // 12. TAG infAdic (Informações Adicionais / Complementares)
        $infoCpl = trim((string) ($dados['info_complementares'] ?? $dados['observacoes'] ?? ($dados->info_complementares ?? '')));
        if (!empty($infoCpl)) {
            $stdInfAdic = new \stdClass();
            $stdInfAdic->infCpl = FormatationUtil::retiraAcentos($infoCpl);
            $nfe->taginfAdic($stdInfAdic);
        }

        // 13. TAG infRespTec (Responsável Técnico do Software)
        $rt = $empresa->responsavelTecnico;
        if ($rt && !empty($rt->cnpj)) {
            $stdRT = new \stdClass();
            $stdRT->CNPJ = preg_replace('/\D/', '', (string) $rt->cnpj);
            $stdRT->xContato = FormatationUtil::retiraAcentos(substr((string) $rt->nome, 0, 60));
            $stdRT->email = substr((string) $rt->email, 0, 60);
            $stdRT->fone = preg_replace('/\D/', '', (string) $rt->telefone);
            $nfe->taginfRespTec($stdRT);
        }

        // 14. Montagem e validação do XML
        try {
            $nfe->montaNFe();
            $erros = $nfe->getErrors();

            if (!empty($erros)) {
                return [
                    'sucesso' => false,
                    'erros'   => $erros,
                ];
            }

            return [
                'sucesso' => true,
                'chave'   => $nfe->getChave(),
                'xml'     => $nfe->getXML(),
                'nNF'     => $numeroNFe,
                'serie'   => $serieNFe,
                'modelo'  => 55,
            ];
        } catch (\Throwable $e) {
            return [
                'sucesso' => false,
                'erros'   => [$e->getMessage()],
            ];
        }
    }

    /**
     * Assina o XML da NF-e com o certificado digital da empresa.
     */
    public function assinarXml(string $xml, Empresa $empresa): string
    {
        $tools = $this->obterTools($empresa);
        return $tools->signNFe($xml);
    }

    /**
     * Transmite a NF-e assinada para a SEFAZ e salva o arquivo autorizado.
     */
    public function transmitir(string $signXml, string $chave, Empresa $empresa, string $caminhoStorage = 'storage/app/nfe/autorizadas'): array
    {
        try {
            $tools = $this->obterTools($empresa);
            $idLote = str_pad((string) mt_rand(1, 99999999), 15, '0', STR_PAD_LEFT);

            // Envia o lote de forma síncrona/assíncrona
            $resp = $tools->sefazEnviaLote([$signXml], $idLote);

            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 103) {
                return [
                    'sucesso' => false,
                    'erro'    => "[$std->cStat] - $std->xMotivo",
                ];
            }

            $recibo = $std->infRec->nRec;
            sleep(2);

            $protocolo = $tools->sefazConsultaRecibo($recibo);
            $xmlAutorizado = Complements::toAuthorize($signXml, $protocolo);

            $dir = base_path($caminhoStorage);
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }
            file_put_contents($dir . DIRECTORY_SEPARATOR . $chave . '.xml', $xmlAutorizado);

            return [
                'sucesso'        => true,
                'recibo'         => $recibo,
                'xml_autorizado' => $xmlAutorizado,
            ];
        } catch (\Throwable $e) {
            return [
                'sucesso' => false,
                'erro'    => $e->getMessage(),
            ];
        }
    }

    /**
     * Inutiliza uma faixa de numeração de NF-e na SEFAZ.
     */
    public function inutilizarNum(int $serie, int $numI, int $numF, string $xJust, Empresa $empresa): array
    {
        try {
            $tools = $this->obterTools($empresa);
            $response = $tools->sefazInutiliza($serie, $numI, $numF, $xJust);

            $st = new Standardize($response);
            $std = $st->toStd();
            $arr = $st->toArray();

            return [
                'sucesso' => in_array($std->infInut->cStat ?? null, [102, 563]),
                'dados'   => $arr,
            ];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    /**
     * Emite Carta de Correção Eletrônica (CC-e) para uma NF-e.
     */
    public function cartaCorrecao(string $chave, string $justificativa, int $nSeqEvento, Empresa $empresa): array
    {
        try {
            $tools = $this->obterTools($empresa);
            $response = $tools->sefazCCe($chave, $justificativa, $nSeqEvento);

            $st = new Standardize($response);
            $std = $st->toStd();
            $arr = $st->toArray();

            $cStat = $std->retEvento->infEvento->cStat ?? null;
            return [
                'sucesso' => in_array($cStat, ['135', '136']),
                'dados'   => $arr,
            ];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    /**
     * Cancela uma NF-e previamente autorizada.
     */
    public function cancelar(string $chave, string $justificativa, string $nProt, Empresa $empresa): array
    {
        try {
            $tools = $this->obterTools($empresa);
            $response = $tools->sefazCancela($chave, $justificativa, $nProt);

            $st = new Standardize($response);
            $std = $st->toStd();
            $arr = $st->toArray();

            $cStat = $std->retEvento->infEvento->cStat ?? null;
            return [
                'sucesso' => in_array($cStat, ['101', '135', '155']),
                'dados'   => $arr,
            ];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    // --- MÉTODOS AUXILIARES ---

    private function resolverEmpresa(array|NotaFiscal $dados, ?Empresa $empresa): ?Empresa
    {
        if ($empresa) {
            return $empresa->loadMissing('endereco', 'preferencia', 'responsavelTecnico');
        }

        if ($dados instanceof NotaFiscal && $dados->empresa_id) {
            return Empresa::with('endereco', 'preferencia', 'responsavelTecnico')->find($dados->empresa_id);
        }

        if (is_array($dados)) {
            $id = $dados['empresa_id'] ?? ($dados['empresa']['id'] ?? null);
            if ($id) {
                return Empresa::with('endereco', 'preferencia', 'responsavelTecnico')->find($id);
            }
        }

        return Empresa::with('endereco', 'preferencia', 'responsavelTecnico')->first();
    }

    private function resolverCliente(array|NotaFiscal $dados): ?Cliente
    {
        if ($dados instanceof NotaFiscal && $dados->cliente_id) {
            return Cliente::with('endereco')->find($dados->cliente_id);
        }

        if (is_array($dados)) {
            $id = $dados['cliente_id'] ?? ($dados['cliente']['id'] ?? null);
            if ($id) {
                return Cliente::with('endereco')->find($id);
            }
        }

        return null;
    }

    private function resolverItens(array|NotaFiscal $dados): array
    {
        if ($dados instanceof NotaFiscal) {
            return $dados->itens()->with('produto')->get()->toArray();
        }

        return $dados['itens'] ?? [];
    }

    private function obterEnderecoPadrao(): Endereco
    {
        $endereco = Endereco::first();
        if ($endereco) {
            return $endereco;
        }

        $padrao = new Endereco();
        $padrao->logradouro = 'Rua Principal';
        $padrao->numero = 'S/N';
        $padrao->bairro = 'Centro';
        $padrao->cidade = 'Garanhuns';
        $padrao->uf = 'PE';
        $padrao->cep = '55299560';
        $padrao->ibge = '2606002';
        return $padrao;
    }
}
