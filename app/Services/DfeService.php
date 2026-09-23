<?php

namespace App\Services;

use App\Models\DfeDocumento;
use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Tools;
use RuntimeException;
use SimpleXMLElement;

class DfeService
{
    /**
     * Mapeamento de códigos de eventos de manifestação do destinatário.
     */
    public const EVENTO_CONFIRMACAO   = '210200';
    public const EVENTO_CIENCIA       = '210210';
    public const EVENTO_DESCONHECIDO  = '210220';
    public const EVENTO_NAO_REALIZADO = '210240';

    /**
     * Instancia o componente Tools do sped-nfe para a empresa informada.
     */
    public function inicializarTools(Empresa $empresa): Tools
    {
        $preferencia = $empresa->preferencia ?? EmpresaPreferencia::where('empresa_id', $empresa->id)->first();

        if (!$preferencia) {
            throw new RuntimeException("Empresa [{$empresa->razao_social}] não possui registro de preferências fiscais configurado.");
        }

        $certificadoConteudo = $this->obterCertificadoConteudo($empresa, $preferencia);
        $senhaCertificado = $this->obterSenhaCertificado($preferencia);

        try {
            $certificate = Certificate::readPfx($certificadoConteudo, $senhaCertificado);
        } catch (\Throwable $e) {
            throw new RuntimeException("Falha ao ler o Certificado Digital da empresa [{$empresa->razao_social}]: " . $e->getMessage(), 0, $e);
        }

        $cnpjLimpo = preg_replace('/\D/', '', (string) $empresa->cnpj);
        $uf = strtoupper(trim($empresa->endereco->uf ?? 'PE'));

        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) ($preferencia->ambiente_dfe ?? 1),
            'razaosocial' => $empresa->razao_social,
            'siglaUF'     => $uf,
            'cnpj'        => $cnpjLimpo,
            'schemes'     => 'PL_009_V4',
            'versao'      => '4.00',
            'tokenIBPT'   => '',
            'CSC'         => '',
            'CSCid'       => '',
        ];

        $tools = new Tools(json_encode($config), $certificate);
        $tools->model('55');

        return $tools;
    }

    /**
     * Executa a sincronização de um lote de documentos DF-e na SEFAZ.
     *
     * @param Empresa $empresa
     * @param bool $ignorarIntervalo Se true, ignora a trava de 60 minutos (uso manual)
     * @return array
     */
    public function sincronizarLote(Empresa $empresa, bool $ignorarIntervalo = false): array
    {
        $preferencia = $empresa->preferencia ?? EmpresaPreferencia::firstOrCreate(['empresa_id' => $empresa->id]);

        // Regra antibloqueio SEFAZ (Rejeição 656 - Consumo Indevido):
        // Se a última consulta retornou 137 (nenhum documento), deve-se aguardar pelo menos 60 minutos
        if (!$ignorarIntervalo && $preferencia->cstat_ultima_consulta_dfe === '137' && $preferencia->data_ultima_consulta_dfe) {
            $minutosDesdeUltima = Carbon::now()->diffInMinutes($preferencia->data_ultima_consulta_dfe);
            if ($minutosDesdeUltima < 60) {
                $restante = 60 - $minutosDesdeUltima;
                return [
                    'sucesso'   => true,
                    'bloqueado' => true,
                    'cStat'     => '137',
                    'mensagem'  => "Aguarde o intervalo da SEFAZ para evitar Rejeição 656. Próxima consulta em {$restante} minuto(s).",
                    'ultNSU'    => $preferencia->ult_nsu,
                    'maxNSU'    => $preferencia->max_nsu,
                ];
            }
        }

        try {
            $tools = $this->inicializarTools($empresa);
        } catch (\Throwable $e) {
            return [
                'sucesso'  => false,
                'erro'     => $e->getMessage(),
            ];
        }

        $ultNSU = str_pad((string) ($preferencia->ult_nsu ?: '0'), 15, '0', STR_PAD_LEFT);

        try {
            $response = $tools->sefazDistDFe($ultNSU);
            $st = new Standardize($response);
            $std = $st->toStd();

            $preferencia->data_ultima_consulta_dfe = Carbon::now();
            $preferencia->cstat_ultima_consulta_dfe = (string) ($std->cStat ?? '');
            $preferencia->motivo_ultima_consulta_dfe = (string) ($std->xMotivo ?? '');

            $cStat = (string) ($std->cStat ?? '');

            // cStat 138: Documento(s) localizado(s)
            if ($cStat === '138') {
                $novoUltNSU = (string) ($std->ultNSU ?? $preferencia->ult_nsu);
                $novoMaxNSU = (string) ($std->maxNSU ?? $preferencia->max_nsu);

                $preferencia->ult_nsu = $novoUltNSU;
                $preferencia->max_nsu = $novoMaxNSU;
                $preferencia->save();

                $documentosExtraidos = $this->extrairDocZips($response);
                $qtdProcessados = 0;

                foreach ($documentosExtraidos as $docItem) {
                    $this->processarDocumentoXml($empresa, $docItem['nsu'], $docItem['schema'], $docItem['xml']);
                    $qtdProcessados++;
                }

                $temMais = ((int) $novoUltNSU < (int) $novoMaxNSU);

                return [
                    'sucesso'        => true,
                    'cStat'          => '138',
                    'qtdProcessados' => $qtdProcessados,
                    'ultNSU'         => $novoUltNSU,
                    'maxNSU'         => $novoMaxNSU,
                    'temMais'        => $temMais,
                    'mensagem'       => "Sincronização concluída: {$qtdProcessados} documento(s) processado(s).",
                ];
            }

            // cStat 137: Nenhum documento localizado
            if ($cStat === '137') {
                $preferencia->ult_nsu = (string) ($std->ultNSU ?? $preferencia->ult_nsu);
                $preferencia->max_nsu = (string) ($std->maxNSU ?? $preferencia->max_nsu);
                $preferencia->save();

                return [
                    'sucesso'  => true,
                    'cStat'    => '137',
                    'ultNSU'   => $preferencia->ult_nsu,
                    'maxNSU'   => $preferencia->max_nsu,
                    'temMais'  => false,
                    'mensagem' => 'Nenhum novo documento localizado na SEFAZ para este NSU.',
                ];
            }

            // Rejeição 656: Consumo Indevido
            if ($cStat === '656') {
                $preferencia->save();
                return [
                    'sucesso'  => false,
                    'cStat'    => '656',
                    'erro'     => 'Rejeição 656: Consumo Indevido pela SEFAZ. Aguarde 60 minutos antes de consultar novamente.',
                ];
            }

            $preferencia->save();

            return [
                'sucesso' => false,
                'cStat'   => $cStat,
                'erro'    => "[{$cStat}] " . ($std->xMotivo ?? 'Erro desconhecido na SEFAZ'),
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao sincronizar DF-e: ' . $e->getMessage(), [
                'empresa_id' => $empresa->id,
                'ultNSU'     => $ultNSU,
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'sucesso' => false,
                'erro'    => 'Falha na comunicação com a SEFAZ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processa um XML individual vindo do lote descompactado da SEFAZ.
     */
    public function processarDocumentoXml(Empresa $empresa, string $nsu, string $schema, string $xml): ?DfeDocumento
    {
        try {
            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($xml);
            libxml_clear_errors();

            if (!$xmlObj) {
                return null;
            }

            $rootName = $xmlObj->getName();

            // 1. Resumo da NF-e (resNFe)
            if ($schema === 'resNFe' || str_contains($schema, 'resNFe') || $rootName === 'resNFe') {
                $chave = (string) $xmlObj->chNFe;
                $cnpj = (string) ($xmlObj->CNPJ ?? $xmlObj->CPF ?? '');
                $nome = (string) $xmlObj->xNome;
                $ie = (string) ($xmlObj->IE ?? '');
                $valor = (float) ($xmlObj->vNF ?? 0);
                $dhEmi = (string) $xmlObj->dhEmi;
                $tpNF = isset($xmlObj->tpNF) ? (int) $xmlObj->tpNF : 1;
                $cSitNFe = isset($xmlObj->cSitNFe) ? (int) $xmlObj->cSitNFe : 1;

                return DfeDocumento::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'chave'      => $chave,
                        'schema'     => 'resNFe',
                    ],
                    [
                        'nsu'            => $nsu,
                        'tipo_documento' => 'NFE',
                        'cnpj_emitente'  => $cnpj,
                        'nome_emitente'  => $nome,
                        'ie_emitente'    => $ie,
                        'valor_total'    => $valor,
                        'data_emissao'   => $dhEmi ? Carbon::parse($dhEmi) : null,
                        'tipo_nfe'       => $tpNF,
                        'situacao_nfe'   => $cSitNFe,
                    ]
                );
            }

            // 2. NF-e Completa (procNFe / nfeProc)
            if (in_array($schema, ['procNFe', 'nfeProc']) || str_contains($schema, 'procNFe') || str_contains($schema, 'nfeProc') || in_array($rootName, ['nfeProc', 'NFe'])) {
                $infNFe = $xmlObj->NFe->infNFe ?? $xmlObj->infNFe ?? null;
                $protNFe = $xmlObj->protNFe->infProt ?? null;

                $chave = (string) ($protNFe->chNFe ?? (isset($infNFe['Id']) ? preg_replace('/\D/', '', (string) $infNFe['Id']) : ''));
                $cnpj = (string) ($infNFe->emit->CNPJ ?? $infNFe->emit->CPF ?? '');
                $nome = (string) ($infNFe->emit->xNome ?? '');
                $ie = (string) ($infNFe->emit->IE ?? '');
                $valor = (float) ($infNFe->total->ICMSTot->vNF ?? 0);
                $dhEmi = (string) ($infNFe->ide->dhEmi ?? '');
                $tpNF = isset($infNFe->ide->tpNF) ? (int) $infNFe->ide->tpNF : 1;

                // Salva o documento com schema procNFe e o XML completo
                $doc = DfeDocumento::updateOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'chave'      => $chave,
                        'schema'     => 'procNFe',
                    ],
                    [
                        'nsu'            => $nsu,
                        'tipo_documento' => 'NFE',
                        'cnpj_emitente'  => $cnpj,
                        'nome_emitente'  => $nome,
                        'ie_emitente'    => $ie,
                        'valor_total'    => $valor,
                        'data_emissao'   => $dhEmi ? Carbon::parse($dhEmi) : null,
                        'tipo_nfe'       => $tpNF,
                        'situacao_nfe'   => 1,
                        'xml'            => $xml,
                    ]
                );

                // Atualiza também o registro resNFe existente (se houver), injetando o XML
                DfeDocumento::where('empresa_id', $empresa->id)
                    ->where('chave', $chave)
                    ->where('schema', 'resNFe')
                    ->update(['xml' => $xml]);

                return $doc;
            }

            // 3. Evento (Cancelamento, Carta de Correção, etc.)
            if (in_array($schema, ['resEvento', 'procEventoNFe']) || str_contains($schema, 'Evento') || in_array($rootName, ['resEvento', 'procEventoNFe'])) {
                $infEvento = $xmlObj->retEvento->infEvento ?? $xmlObj->evento->infEvento ?? $xmlObj;
                $chave = (string) ($infEvento->chNFe ?? '');
                $tpEvento = (string) ($infEvento->tpEvento ?? '');

                // Se o evento for de Cancelamento (110111), marca a nota como cancelada
                if ($tpEvento === '110111' && $chave) {
                    DfeDocumento::where('empresa_id', $empresa->id)
                        ->where('chave', $chave)
                        ->update(['situacao_nfe' => 2]);
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning("Erro ao processar docZip DF-e [NSU: {$nsu}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Envia um evento de Manifestação do Destinatário para a SEFAZ.
     *
     * @param Empresa $empresa
     * @param string $chave
     * @param string $tipoEvento 210200 (Confirmação), 210210 (Ciência), 210220 (Desconhecimento), 210240 (Não Realizada)
     * @param string $justificativa Obrigatória apenas para 210240 (mínimo 15 caracteres)
     * @return array
     */
    public function manifestar(Empresa $empresa, string $chave, string $tipoEvento, string $justificativa = ''): array
    {
        $eventosValidos = [
            self::EVENTO_CONFIRMACAO,
            self::EVENTO_CIENCIA,
            self::EVENTO_DESCONHECIDO,
            self::EVENTO_NAO_REALIZADO,
        ];

        if (!in_array($tipoEvento, $eventosValidos, true)) {
            return [
                'sucesso' => false,
                'erro'    => 'Tipo de evento de manifestação inválido: ' . $tipoEvento,
            ];
        }

        if ($tipoEvento === self::EVENTO_NAO_REALIZADO && strlen(trim($justificativa)) < 15) {
            return [
                'sucesso' => false,
                'erro'    => 'A justificativa para Operação Não Realizada deve conter no mínimo 15 caracteres.',
            ];
        }

        try {
            $tools = $this->inicializarTools($empresa);
            $response = $tools->sefazManifesta($chave, $tipoEvento, $justificativa, 1);

            $st = new Standardize($response);
            $std = $st->toStd();

            $nomeSituacao = match ($tipoEvento) {
                self::EVENTO_CIENCIA       => 'ciencia',
                self::EVENTO_CONFIRMACAO   => 'confirmada',
                self::EVENTO_DESCONHECIDO  => 'desconhecida',
                self::EVENTO_NAO_REALIZADO => 'nao_realizada',
                default                    => 'sem_manifestacao',
            };

            // Status 128: Lote de Evento Processado
            if (isset($std->cStat) && (string) $std->cStat === '128') {
                $infEvento = $std->retEvento->infEvento ?? null;
                $cStatEvento = (string) ($infEvento->cStat ?? '');
                $xMotivoEvento = (string) ($infEvento->xMotivo ?? '');
                $protocolo = (string) ($infEvento->nProt ?? '');

                // 135 ou 136: Evento registrado e vinculado
                if (in_array($cStatEvento, ['135', '136'], true)) {
                    DfeDocumento::where('empresa_id', $empresa->id)
                        ->where('chave', $chave)
                        ->update([
                            'situacao_manifestacao'  => $nomeSituacao,
                            'data_manifestacao'      => Carbon::now(),
                            'protocolo_manifestacao' => $protocolo,
                            'mensagem_manifestacao'  => $xMotivoEvento,
                        ]);

                    return [
                        'sucesso'    => true,
                        'cStat'      => $cStatEvento,
                        'protocolo'  => $protocolo,
                        'situacao'   => $nomeSituacao,
                        'mensagem'   => $xMotivoEvento,
                    ];
                }

                // 573: Rejeição - Duplicidade de evento (já estava registrado)
                if ($cStatEvento === '573') {
                    DfeDocumento::where('empresa_id', $empresa->id)
                        ->where('chave', $chave)
                        ->update([
                            'situacao_manifestacao' => $nomeSituacao,
                            'data_manifestacao'     => Carbon::now(),
                            'mensagem_manifestacao' => $xMotivoEvento,
                        ]);

                    return [
                        'sucesso'  => true,
                        'cStat'    => '573',
                        'situacao' => $nomeSituacao,
                        'mensagem' => 'Evento já estava registrado anteriormente na SEFAZ.',
                    ];
                }

                return [
                    'sucesso' => false,
                    'cStat'   => $cStatEvento,
                    'erro'    => "[{$cStatEvento}] {$xMotivoEvento}",
                ];
            }

            $cStatGeral = (string) ($std->cStat ?? '');
            $xMotivoGeral = (string) ($std->xMotivo ?? 'Erro ao manifestar na SEFAZ');

            return [
                'sucesso' => false,
                'cStat'   => $cStatGeral,
                'erro'    => "[{$cStatGeral}] {$xMotivoGeral}",
            ];
        } catch (\Throwable $e) {
            Log::error("Erro ao manifestar NF-e [{$chave}]: " . $e->getMessage());

            return [
                'sucesso' => false,
                'erro'    => 'Falha na comunicação com a SEFAZ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta especificamente uma chave de acesso na SEFAZ para obter o XML completo.
     */
    public function consultarPorChave(Empresa $empresa, string $chave): array
    {
        try {
            $tools = $this->inicializarTools($empresa);
            $response = $tools->sefazDistDFe('0', null, $chave);
            $st = new Standardize($response);
            $std = $st->toStd();

            if (isset($std->cStat) && (string) $std->cStat === '138') {
                $documentosExtraidos = $this->extrairDocZips($response);

                foreach ($documentosExtraidos as $docItem) {
                    $documento = $this->processarDocumentoXml($empresa, $docItem['nsu'], $docItem['schema'], $docItem['xml']);
                    if ($documento && $documento->xml) {
                        return [
                            'sucesso'   => true,
                            'xml'       => $documento->xml,
                            'documento' => $documento,
                        ];
                    }
                }
            }

            return [
                'sucesso'  => false,
                'cStat'    => (string) ($std->cStat ?? ''),
                'mensagem' => (string) ($std->xMotivo ?? 'XML ainda não disponibilizado pela SEFAZ.'),
            ];
        } catch (\Throwable $e) {
            return [
                'sucesso' => false,
                'erro'    => $e->getMessage(),
            ];
        }
    }

    /**
     * Extrai os documentos compactados do XML de retorno da SEFAZ (<docZip>).
     * Utiliza DOMDocument para evitar falhas de conversão de atributos em stdClass.
     *
     * @param string $rawXml
     * @return array Array de itens contendo ['nsu', 'schema', 'xml']
     */
    protected function extrairDocZips(string $rawXml): array
    {
        $resultados = [];

        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($rawXml);
            libxml_clear_errors();

            $docZips = $dom->getElementsByTagName('docZip');

            foreach ($docZips as $docZip) {
                /** @var \DOMElement $docZip */
                $nsu = $docZip->getAttribute('NSU') ?: $docZip->getAttribute('nsu') ?: '0';
                $schema = $docZip->getAttribute('schema') ?: '';
                $base64 = trim($docZip->nodeValue ?? '');

                if (empty($base64)) {
                    continue;
                }

                $descompactado = @gzdecode(base64_decode($base64));

                if (!empty($descompactado)) {
                    $resultados[] = [
                        'nsu'    => $nsu,
                        'schema' => $schema,
                        'xml'    => $descompactado,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao extrair docZips do XML da SEFAZ: ' . $e->getMessage());
        }

        return $resultados;
    }

    /**
     * Localiza o arquivo de Certificado Digital e retorna seus bytes.
     */
    protected function obterCertificadoConteudo(Empresa $empresa, EmpresaPreferencia $preferencia): string
    {
        $caminhosPossiveis = [];

        if (!empty($preferencia->certificado_digital)) {
            $caminhosPossiveis[] = storage_path('app/' . $preferencia->certificado_digital);
            $caminhosPossiveis[] = storage_path('app/public/' . $preferencia->certificado_digital);
            $caminhosPossiveis[] = storage_path('app/public/certificados/' . $preferencia->certificado_digital);
            $caminhosPossiveis[] = storage_path('app/certificados/' . $preferencia->certificado_digital);
            $caminhosPossiveis[] = public_path('certificados/' . $preferencia->certificado_digital);
            $caminhosPossiveis[] = $preferencia->certificado_digital; // Caminho absoluto
        }

        // Padrão do projeto baseado na razão social da empresa
        $caminhosPossiveis[] = storage_path('app/public/certificados/' . $empresa->razao_social . '.pfx');
        $caminhosPossiveis[] = storage_path('app/certificados/' . $empresa->razao_social . '.pfx');

        foreach ($caminhosPossiveis as $caminho) {
            if (File::exists($caminho) && File::isFile($caminho)) {
                $conteudo = file_get_contents($caminho);
                if ($conteudo !== false && strlen($conteudo) > 0) {
                    return $conteudo;
                }
            }
        }

        throw new RuntimeException("Arquivo de Certificado Digital (.pfx) não foi encontrado no servidor para a empresa [{$empresa->razao_social}].");
    }

    /**
     * Retorna a senha do Certificado Digital descriptografada.
     */
    protected function obterSenhaCertificado(EmpresaPreferencia $preferencia): string
    {
        $senha = $preferencia->senha_certificado;

        if (empty($senha)) {
            return '';
        }

        try {
            return Crypt::decryptString($senha);
        } catch (\Throwable $e) {
            // Se não estiver criptografada, usa em texto plano
            return $senha;
        }
    }
}
