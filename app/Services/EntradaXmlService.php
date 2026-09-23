<?php

namespace App\Services;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\AlmoxarifadoMovimentacao;
use App\Models\CategoriaProduto;
use App\Models\ContasAPagar;
use App\Models\DfeDocumento;
use App\Models\Empresa;
use App\Models\Entrada;
use App\Models\Estoque;
use App\Models\Fornecedor;
use App\Models\ItemEntrada;
use App\Models\ParcelaContasAPagar;
use App\Models\Produto;
use App\Services\DfeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SimpleXMLElement;

class EntradaXmlService
{
    /**
     * Faz o parser do arquivo ou string XML da NF-e e retorna array estruturado.
     */
    public function parseXml(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        libxml_clear_errors();

        if (!$xml) {
            throw new RuntimeException('Conteúdo XML inválido ou corrompido.');
        }

        $infNFe = $xml->NFe->infNFe ?? $xml->infNFe ?? null;
        $protNFe = $xml->protNFe->infProt ?? null;

        if (!$infNFe) {
            throw new RuntimeException('Estrutura de NF-e não encontrada no arquivo XML.');
        }

        // Chave de acesso
        $chave = (string) ($protNFe->chNFe ?? (isset($infNFe['Id']) ? preg_replace('/\D/', '', (string) $infNFe['Id']) : ''));

        // Dados da Nota (IDE)
        $ide = $infNFe->ide;
        $numeroNota = (string) ($ide->nNF ?? '');
        $serie = (string) ($ide->serie ?? '');
        $natOp = (string) ($ide->natOp ?? '');
        $dhEmi = (string) ($ide->dhEmi ?? '');
        $dhSaiEnt = (string) ($ide->dhSaiEnt ?? $dhEmi);

        // Dados do Emitente (Fornecedor)
        $emit = $infNFe->emit;
        $cnpj = (string) ($emit->CNPJ ?? $emit->CPF ?? '');
        $razaoSocial = (string) ($emit->xNome ?? '');
        $nomeFantasia = (string) ($emit->xFant ?? $razaoSocial);
        $ie = (string) ($emit->IE ?? '');

        $enderEmit = $emit->enderEmit ?? null;
        $enderecoCompleto = '';
        if ($enderEmit) {
            $enderecoCompleto = trim(sprintf(
                '%s, %s %s - %s, %s/%s - CEP: %s',
                (string) ($enderEmit->xLgr ?? ''),
                (string) ($enderEmit->nro ?? ''),
                (string) ($enderEmit->xCpl ?? ''),
                (string) ($enderEmit->xBairro ?? ''),
                (string) ($enderEmit->xMun ?? ''),
                (string) ($enderEmit->UF ?? ''),
                (string) ($enderEmit->CEP ?? '')
            ), ', -');
        }

        // Totais da Nota
        $totais = $infNFe->total->ICMSTot ?? null;
        $vProd = (float) ($totais->vProd ?? 0);
        $vFrete = (float) ($totais->vFrete ?? 0);
        $vSeg = (float) ($totais->vSeg ?? 0);
        $vDesc = (float) ($totais->vDesc ?? 0);
        $vOutro = (float) ($totais->vOutro ?? 0);
        $vICMS = (float) ($totais->vICMS ?? 0);
        $vST = (float) ($totais->vST ?? 0);
        $vIPI = (float) ($totais->vIPI ?? 0);
        $vNF = (float) ($totais->vNF ?? 0);

        // Itens / Produtos da Nota
        $itens = [];
        $dets = isset($infNFe->det) ? (is_array($infNFe->det) ? $infNFe->det : [$infNFe->det]) : [];

        foreach ($dets as $index => $det) {
            $prod = $det->prod;
            $imposto = $det->imposto;

            $numeroItem = (int) ($det['nItem'] ?? ($index + 1));
            $cProd = (string) ($prod->cProd ?? '');
            $cEAN = (string) ($prod->cEAN ?? '');
            if (strtoupper($cEAN) === 'SEM GTIN') {
                $cEAN = '';
            }
            $xProd = (string) ($prod->xProd ?? '');
            $ncm = (string) ($prod->NCM ?? '');
            $cest = (string) ($prod->CEST ?? '');
            $cfop = (string) ($prod->CFOP ?? '');
            $uCom = (string) ($prod->uCom ?? 'UN');
            $qCom = (float) ($prod->qCom ?? 1);
            $vUnCom = (float) ($prod->vUnCom ?? 0);
            $vProdItem = (float) ($prod->vProd ?? 0);
            $vDescItem = (float) ($prod->vDesc ?? 0);
            $vFreteItem = (float) ($prod->vFrete ?? 0);
            $vSegItem = (float) ($prod->vSeg ?? 0);
            $vOutroItem = (float) ($prod->vOutro ?? 0);

            // ICMS
            $cstIcms = '';
            $csosn = '';
            $vBCIcms = 0;
            $pIcms = 0;
            $vIcms = 0;
            $vBCST = 0;
            $pICMSST = 0;
            $vICMSST = 0;

            if (isset($imposto->ICMS)) {
                $icmsGroup = $imposto->ICMS->children()[0] ?? null;
                if ($icmsGroup) {
                    $cstIcms = (string) ($icmsGroup->CST ?? '');
                    $csosn = (string) ($icmsGroup->CSOSN ?? '');
                    $vBCIcms = (float) ($icmsGroup->vBC ?? 0);
                    $pIcms = (float) ($icmsGroup->pICMS ?? 0);
                    $vIcms = (float) ($icmsGroup->vICMS ?? 0);
                    $vBCST = (float) ($icmsGroup->vBCST ?? 0);
                    $pICMSST = (float) ($icmsGroup->pICMSST ?? 0);
                    $vICMSST = (float) ($icmsGroup->vICMSST ?? 0);
                }
            }

            // PIS / COFINS / IPI
            $cstPis = isset($imposto->PIS) ? (string) ($imposto->PIS->children()[0]->CST ?? '') : '';
            $vPis = isset($imposto->PIS) ? (float) ($imposto->PIS->children()[0]->vPIS ?? 0) : 0;
            $cstCofins = isset($imposto->COFINS) ? (string) ($imposto->COFINS->children()[0]->CST ?? '') : '';
            $vCofins = isset($imposto->COFINS) ? (float) ($imposto->COFINS->children()[0]->vCOFINS ?? 0) : 0;
            $cstIpi = isset($imposto->IPI->IPITrib->CST) ? (string) $imposto->IPI->IPITrib->CST : '';
            $vIpi = isset($imposto->IPI->IPITrib->vIPI) ? (float) $imposto->IPI->IPITrib->vIPI : 0;

            // Reforma Tributária (IBS / CBS)
            $ibsCbs = $imposto->IBSCBS ?? null;
            $cstIbsCbs = isset($ibsCbs->CST) ? (string) $ibsCbs->CST : '000';
            $cClassTrib = isset($ibsCbs->cClassTrib) ? (string) $ibsCbs->cClassTrib : '000001';
            $pIBS = 0.1;
            $pCBS = 0.9;
            if (isset($ibsCbs->gIBSCBS->gIBSUF->pIBSUF) || isset($ibsCbs->gIBSCBS->gIBSMun->pIBSMun)) {
                $pIBS = (float) ($ibsCbs->gIBSCBS->gIBSUF->pIBSUF ?? 0) + (float) ($ibsCbs->gIBSCBS->gIBSMun->pIBSMun ?? 0);
            }
            if (isset($ibsCbs->gIBSCBS->gCBS->pCBS)) {
                $pCBS = (float) $ibsCbs->gIBSCBS->gCBS->pCBS;
            }

            $itens[] = [
                'numero_item'           => $numeroItem,
                'codigo_fornecedor'     => $cProd,
                'codigo_barras'         => $cEAN,
                'descricao'             => $xProd,
                'ncm'                   => $ncm,
                'cest'                  => $cest,
                'cfop'                  => $cfop,
                'unidade'               => $uCom,
                'quantidade'            => $qCom,
                'valor_unitario'        => $vUnCom,
                'valor_total'           => $vProdItem,
                'valor_desconto'        => $vDescItem,
                'valor_frete'           => $vFreteItem,
                'valor_seguro'          => $vSegItem,
                'valor_outras_despesas' => $vOutroItem,
                'cst_icms'              => $cstIcms,
                'csosn'                 => $csosn,
                'base_icms'             => $vBCIcms,
                'aliquota_icms'         => $pIcms,
                'valor_icms'            => $vIcms,
                'base_icms_st'          => $vBCST,
                'aliquota_icms_st'      => $pICMSST,
                'valor_icms_st'         => $vICMSST,
                'cst_pis'               => $cstPis,
                'valor_pis'             => $vPis,
                'cst_cofins'            => $cstCofins,
                'valor_cofins'          => $vCofins,
                'cst_ipi'               => $cstIpi,
                'valor_ipi'             => $vIpi,
                'cClassTrib'            => $cClassTrib,
                'pIBS'                  => $pIBS,
                'pCBS'                  => $pCBS,
                'cst_ibs_cbs'           => $cstIbsCbs,
            ];
        }

        // Faturas / Duplicatas (Cobrança)
        $duplicatas = [];
        if (isset($infNFe->cobr->dup)) {
            $dups = is_array($infNFe->cobr->dup) ? $infNFe->cobr->dup : [$infNFe->cobr->dup];
            foreach ($dups as $dup) {
                $duplicatas[] = [
                    'numero'          => (string) ($dup->nDup ?? ''),
                    'data_vencimento' => (string) ($dup->dVenc ?? ''),
                    'valor'           => (float) ($dup->vDup ?? 0),
                ];
            }
        }

        return [
            'chave'            => $chave,
            'numero_nota'      => $numeroNota,
            'serie'            => $serie,
            'natureza_operacao'=> $natOp,
            'data_emissao'     => $dhEmi,
            'data_entrada'     => $dhSaiEnt,
            'fornecedor'       => [
                'cnpj'          => $cnpj,
                'razao_social'  => $razaoSocial,
                'nome_fantasia' => $nomeFantasia,
                'ie'            => $ie,
                'endereco'      => $enderecoCompleto,
            ],
            'totais'           => [
                'valor_produtos'        => $vProd,
                'valor_frete'           => $vFrete,
                'valor_seguro'          => $vSeg,
                'valor_desconto'        => $vDesc,
                'valor_outras_despesas' => $vOutro,
                'valor_icms'            => $vICMS,
                'valor_icms_st'         => $vST,
                'valor_ipi'             => $vIPI,
                'valor_total'           => $vNF,
            ],
            'itens'            => $itens,
            'duplicatas'       => $duplicatas,
            'xml'              => $xmlContent,
        ];
    }

    /**
     * Localiza um Fornecedor pelo CNPJ ou cria um novo cadastro com os dados do XML.
     */
    public function obterOuCriarFornecedor(array $dadosFornecedor): Fornecedor
    {
        $cnpjLimpo = preg_replace('/\D/', '', (string) ($dadosFornecedor['cnpj'] ?? ''));

        if (!empty($cnpjLimpo)) {
            $fornecedor = Fornecedor::where('cnpj', $cnpjLimpo)
                ->orWhere('cnpj', (string) ($dadosFornecedor['cnpj'] ?? ''))
                ->first();

            if (!$fornecedor) {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'mysql') {
                    $fornecedor = Fornecedor::whereRaw("REGEXP_REPLACE(cnpj, '[^0-9]', '') = ?", [$cnpjLimpo])->first();
                } else {
                    $fornecedor = Fornecedor::whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', ''), ' ', '') = ?", [$cnpjLimpo])->first();
                }
            }

            if ($fornecedor) {
                return $fornecedor;
            }
        }

        return Fornecedor::create([
            'razao_social'       => $dadosFornecedor['razao_social'] ?? 'Fornecedor sem razão social',
            'nome_fantasia'      => $dadosFornecedor['nome_fantasia'] ?? ($dadosFornecedor['razao_social'] ?? null),
            'cnpj'               => $dadosFornecedor['cnpj'] ?? null,
            'inscricao_estadual' => $dadosFornecedor['ie'] ?? 'ISENTO',
            'endereco'           => $dadosFornecedor['endereco'] ?? null,
        ]);
    }

    /**
     * Processa e consolida a entrada da nota no sistema:
     * 1. Cria a Entrada
     * 2. Cria os Itens da Entrada
     * 3. Alimenta o Estoque (Venda/Produtos) ou Almoxarifado (Consumo)
     * 4. Gera Contas a Pagar (se solicitado)
     * 5. Vincula ao DF-e Documento
     */
    public function processarEntrada(array $dadosPayload, int $empresaId, int $usuarioId): Entrada
    {
        $entrada = DB::transaction(function () use ($dadosPayload, $empresaId, $usuarioId) {
            $parsed = $this->parseXml($dadosPayload['xml']);

            // 1. Fornecedor
            $fornecedor = $this->obterOuCriarFornecedor($parsed['fornecedor']);

            // 2. Cabeçalho da Entrada
            $entrada = Entrada::create([
                'empresa_id'            => $empresaId,
                'fornecedor_id'         => $fornecedor->id,
                'usuario_id'            => $usuarioId,
                'dfe_documento_id'      => $dadosPayload['dfe_documento_id'] ?? null,
                'chave'                 => $parsed['chave'],
                'numero_nota'           => $parsed['numero_nota'],
                'serie'                 => $parsed['serie'] ?? null,
                'natureza_operacao'     => $parsed['natureza_operacao'] ?? null,
                'data_emissao'          => $parsed['data_emissao'] ? Carbon::parse($parsed['data_emissao']) : now(),
                'data_entrada'          => !empty($dadosPayload['data_entrada']) ? Carbon::parse($dadosPayload['data_entrada']) : now(),
                'valor_produtos'        => $parsed['totais']['valor_produtos'],
                'valor_frete'           => $parsed['totais']['valor_frete'],
                'valor_seguro'          => $parsed['totais']['valor_seguro'],
                'valor_desconto'        => $parsed['totais']['valor_desconto'],
                'valor_outras_despesas' => $parsed['totais']['valor_outras_despesas'],
                'valor_icms'            => $parsed['totais']['valor_icms'],
                'valor_icms_st'         => $parsed['totais']['valor_icms_st'],
                'valor_ipi'             => $parsed['totais']['valor_ipi'],
                'valor_total'           => $parsed['totais']['valor_total'],
                'observacoes'           => $dadosPayload['observacoes'] ?? null,
                'xml'                   => $dadosPayload['xml'],
                'status'                => 'confirmada',
            ]);

            // 3. Processamento dos Itens
            $itensMapeados = $dadosPayload['itens'] ?? [];

            foreach ($parsed['itens'] as $idx => $itemParsed) {
                $mapeamento = $itensMapeados[$idx] ?? $itensMapeados[$itemParsed['numero_item']] ?? [];
                $destino = $mapeamento['destino'] ?? 'produto'; // 'produto' ou 'almoxarifado'

                $produtoId = null;
                $almoxarifadoItemId = null;

                if ($destino === 'produto') {
                    $produtoId = $this->obterOuCriarProduto($itemParsed, $mapeamento, $empresaId);
                    $this->incrementarEstoqueProduto($produtoId, $empresaId, (float) $itemParsed['quantidade'], (float) $itemParsed['valor_unitario']);
                } else {
                    $almoxarifadoItemId = $this->obterOuCriarAlmoxarifadoItem($itemParsed, $mapeamento, $empresaId);
                    $this->registrarMovimentacaoAlmoxarifado($almoxarifadoItemId, $empresaId, $usuarioId, (float) $itemParsed['quantidade'], $fornecedor->razao_social, $parsed['numero_nota']);
                }

                ItemEntrada::create(array_merge($itemParsed, [
                    'entrada_id'           => $entrada->id,
                    'empresa_id'           => $empresaId,
                    'produto_id'           => $produtoId,
                    'almoxarifado_item_id' => $almoxarifadoItemId,
                    'destino'              => $destino,
                ]));
            }

            // 4. Geração Automática de Contas a Pagar
            $gerarFinanceiro = !empty($dadosPayload['gerar_contas_a_pagar']);
            $duplicatas = $parsed['duplicatas'];

            if ($gerarFinanceiro && !empty($duplicatas)) {
                $planoContaId = $dadosPayload['plano_de_contas_id'] ?? $fornecedor->plano_de_conta_id ?? null;
                $totalParcelas = count($duplicatas);

                $contaPagar = ContasAPagar::create([
                    'empresa_id'         => $empresaId,
                    'fornecedor_id'      => $fornecedor->id,
                    'plano_de_contas_id' => $planoContaId,
                    'descricao'          => "NF-e {$entrada->numero_nota} - {$fornecedor->razao_social}",
                    'valor'              => $entrada->valor_total,
                    'valor_pago'         => 0,
                    'data_vencimento'    => $duplicatas[0]['data_vencimento'] ?? now()->addMonth()->toDateString(),
                    'status'             => 'pendente',
                    'total_parcelas'     => $totalParcelas,
                ]);

                foreach ($duplicatas as $idx => $dup) {
                    ParcelaContasAPagar::create([
                        'contas_a_pagar_id' => $contaPagar->id,
                        'numero_parcela'    => $dup['numero'] ?: ($idx + 1),
                        'valor'             => (float) $dup['valor'],
                        'data_vencimento'   => $dup['data_vencimento'] ?? now()->addDays(30 * ($idx + 1))->toDateString(),
                        'status'            => 'pendente',
                    ]);
                }
            }

            // 5. Atualização do DfeDocumento (se vinculado)
            if (!empty($dadosPayload['dfe_documento_id'])) {
                DfeDocumento::where('id', $dadosPayload['dfe_documento_id'])
                    ->update([
                        'importado_entrada' => true,
                        'entrada_id'        => $entrada->id,
                    ]);
            } else {
                // Tenta localizar por chave
                DfeDocumento::where('empresa_id', $empresaId)
                    ->where('chave', $entrada->chave)
                    ->update([
                        'importado_entrada' => true,
                        'entrada_id'        => $entrada->id,
                    ]);
            }

            return $entrada;
        });

        // 6. Automação: Manifestar Confirmação da Operação (210200) na SEFAZ se ainda não confirmada
        try {
            $empresa = Empresa::with('preferencia')->find($empresaId);
            if ($empresa && !empty($entrada->chave)) {
                $dfeDoc = DfeDocumento::where('empresa_id', $empresaId)
                    ->where('chave', $entrada->chave)
                    ->first();

                if (!$dfeDoc || $dfeDoc->situacao_manifestacao !== 'confirmada') {
                    app(DfeService::class)->manifestar($empresa, $entrada->chave, DfeService::EVENTO_CONFIRMACAO);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Falha ao registrar manifestação automática de confirmação na SEFAZ [Chave: {$entrada->chave}]: " . $e->getMessage());
        }

        return $entrada;
    }

    /**
     * Localiza ou cria um Produto de venda.
     */
    protected function obterOuCriarProduto(array $itemParsed, array $mapeamento, int $empresaId): int
    {
        if (!empty($mapeamento['produto_id'])) {
            return (int) $mapeamento['produto_id'];
        }

        // Tenta achar por EAN se preenchido
        if (!empty($itemParsed['codigo_barras'])) {
            $prod = Produto::where('empresa_id', $empresaId)
                ->where('ean', $itemParsed['codigo_barras'])
                ->first();
            if ($prod) {
                return $prod->id;
            }
        }

        // Cria novo produto
        $categoriaId = $mapeamento['categoria_produto_id'] ?? CategoriaProduto::first()?->id ?? 1;
        $precoCusto = (float) $itemParsed['valor_unitario'];
        $margem = (float) ($mapeamento['margem_lucro'] ?? 30);
        $precoVenda = $mapeamento['preco_venda'] ?? ($precoCusto * (1 + ($margem / 100)));

        $novoProduto = Produto::create([
            'empresa_id'           => $empresaId,
            'categoria_produto_id' => $categoriaId,
            'descricao'            => $itemParsed['descricao'],
            'ean'                  => $itemParsed['codigo_barras'] ?: null,
            'preco_custo'          => $precoCusto,
            'preco_venda'          => $precoVenda,
            'ncm'                  => $itemParsed['ncm'] ?: null,
            'cst'                  => $itemParsed['cst_icms'] ?: '000',
            'csosn'                => $itemParsed['csosn'] ?: '102',
            'cfop_interno'         => '5102',
            'cfop_externo'         => '6102',
            'ativo'                => true,
        ]);

        return $novoProduto->id;
    }

    /**
     * Incrementa o estoque de um Produto de venda.
     */
    protected function incrementarEstoqueProduto(int $produtoId, int $empresaId, float $quantidade, float $precoCusto): void
    {
        $estoque = Estoque::firstOrCreate(
            ['produto_id' => $produtoId, 'empresa_id' => $empresaId],
            ['estoque_atual' => 0, 'entradas' => 0, 'saidas' => 0]
        );

        $estoque->increment('estoque_atual', $quantidade);
        $estoque->increment('entradas', $quantidade);

        // Atualiza preço de custo no produto
        if ($precoCusto > 0) {
            Produto::where('id', $produtoId)->update(['preco_custo' => $precoCusto]);
        }
    }

    /**
     * Localiza ou cria um Item de Almoxarifado.
     */
    protected function obterOuCriarAlmoxarifadoItem(array $itemParsed, array $mapeamento, int $empresaId): int
    {
        if (!empty($mapeamento['almoxarifado_item_id'])) {
            return (int) $mapeamento['almoxarifado_item_id'];
        }

        // Tenta achar pelo nome
        $itemExistente = AlmoxarifadoItem::where('empresa_id', $empresaId)
            ->where('nome', $itemParsed['descricao'])
            ->first();

        if ($itemExistente) {
            return $itemExistente->id;
        }

        $categoriaId = $mapeamento['almoxarifado_categoria_id'] ?? AlmoxarifadoCategoria::first()?->id ?? 1;

        $novoItem = AlmoxarifadoItem::create([
            'empresa_id'     => $empresaId,
            'categoria_id'   => $categoriaId,
            'nome'           => $itemParsed['descricao'],
            'unidade_medida' => substr($itemParsed['unidade'] ?: 'UN', 0, 20),
            'estoque_atual'  => 0,
            'estoque_minimo' => 0,
            'ativo'          => true,
        ]);

        return $novoItem->id;
    }

    /**
     * Registra movimentação de entrada no Almoxarifado.
     */
    protected function registrarMovimentacaoAlmoxarifado(int $almoxarifadoItemId, int $empresaId, int $usuarioId, float $quantidade, string $fornecedor, string $numeroNota): void
    {
        $item = AlmoxarifadoItem::findOrFail($almoxarifadoItemId);
        $saldoAnterior = (float) $item->estoque_atual;
        $saldoPosterior = $saldoAnterior + $quantidade;

        AlmoxarifadoMovimentacao::create([
            'empresa_id'        => $empresaId,
            'item_id'           => $almoxarifadoItemId,
            'user_id'           => $usuarioId,
            'tipo'              => 'entrada',
            'quantidade'        => $quantidade,
            'saldo_anterior'    => $saldoAnterior,
            'saldo_posterior'   => $saldoPosterior,
            'data_movimentacao' => now(),
            'fornecedor'        => $fornecedor,
            'numero_documento'  => "NF-e {$numeroNota}",
            'observacao'        => 'Entrada via importação de NF-e',
        ]);

        $item->update(['estoque_atual' => $saldoPosterior]);
    }
}
