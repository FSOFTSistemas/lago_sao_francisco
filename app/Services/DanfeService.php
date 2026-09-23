<?php

namespace App\Services;

use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;
use RuntimeException;
use Throwable;

class DanfeService
{
    /**
     * Gera o documento auxiliar da NF-e (DANFE) em formato binário PDF.
     *
     * @param string $xml Conteúdo textual do XML da NF-e (procNFe ou NFe).
     * @param string|null $logoPath Caminho opcional para a imagem do logotipo do emitente.
     * @param bool $cancelada Força a exibição da marca d'água de cancelamento se true.
     * @return string Bytes binários do PDF gerado.
     *
     * @throws InvalidArgumentException Se o XML for vazio ou não contiver estrutura de NF-e.
     * @throws RuntimeException Se ocorrer erro durante a renderização do PDF.
     */
    public function gerarPdf(string $xml, ?string $logoPath = null, bool $cancelada = false): string
    {
        $xml = trim($xml);
        if ($xml === '') {
            throw new InvalidArgumentException('O conteúdo do XML não pode ser vazio para a geração do DANFE.');
        }

        // Verifica presença das tags essenciais de NF-e
        if (!str_contains($xml, '<infNFe') && !str_contains($xml, '<NFe')) {
            throw new InvalidArgumentException('O documento informado não possui a estrutura básica de uma NF-e (<infNFe> ou <NFe>).');
        }

        // Valida a integridade sintática do XML com libxml
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xml);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();

        if ($xmlObj === false) {
            $primeiroErro = !empty($xmlErrors) ? trim($xmlErrors[0]->message) : 'Sintaxe XML malformada.';
            throw new InvalidArgumentException("Falha na estrutura sintática do XML da NF-e: {$primeiroErro}");
        }

        // Configuração dos Parâmetros Gráficos de Layout (A4, Retrato, Margens e Exibição)
        $danfe = new Danfe($xml);
        $danfe->printParameters('P', 'A4', 2, 2);
        $danfe->exibirTextoFatura = true;
        $danfe->exibirPIS = true;
        $danfe->exibirIcmsInterestadual = true;
        $danfe->exibirValorTributos = true;
        $danfe->descProdInfoComplemento = true;
        $danfe->setExibirEmailDestinatario(true);
        $danfe->setOcultarUnidadeTributavel(false);

        if (!empty($logoPath) && file_exists($logoPath)) {
            $danfe->logoParameters($logoPath, 'C');
        }

        // Tratamento de Marca D'água e Status (Cancelamento)
        $isCancelada = $cancelada
            || str_contains($xml, '<tpEvento>110111</tpEvento>')
            || str_contains($xml, '<cStat>101</cStat>');

        if ($isCancelada) {
            $danfe->setCancelFlag(true);
        }

        // 2.5: Renderização Final e Retorno dos Bytes Binários do PDF
        try {
            $pdfContent = $danfe->render();

            if (empty($pdfContent)) {
                throw new RuntimeException('O gerador de PDF retornou um documento vazio.');
            }

            return $pdfContent;
        } catch (Throwable $e) {
            throw new RuntimeException('Falha ao renderizar o DANFE em PDF: ' . $e->getMessage(), 0, $e);
        }
    }
}