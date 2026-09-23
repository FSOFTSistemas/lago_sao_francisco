<?php

namespace App\Services;

use NFePHP\DA\NFe\Danfe;

/**
 * Extensão customizada do DANFE para corrigir o enquadramento geométrico e
 * evitar sobreposição entre a tabela de produtos/serviços e a seção de
 * DADOS ADICIONAIS (Informações Complementares e Reservado ao Fisco).
 */
class LagoDanfe extends Danfe
{
    /**
     * Ajusta a altura da tabela de itens na primeira página para que
     * não ultrapasse o início da seção de DADOS ADICIONAIS.
     *
     * @param  float  $x
     * @param  float  $y
     * @param  int  $nInicio
     * @param  float  $hmax
     * @param  int  $pag
     * @param  int  $totpag
     * @param  int  $hCabecItens
     * @return float
     */
    protected function itens($x, $y, &$nInicio, $hmax, $pag = 0, $totpag = 0, $hCabecItens = 7)
    {
        if ($pag <= 1) {
            $marginf = $this->marginf ?? 2;
            // Altura do rodapé: 4mm | Altura dos dados adicionais: $this->hdadosadic | Título: 4mm | Respiro: 2mm
            $bottomDisponivel = $this->maxH - $marginf - 4 - $this->hdadosadic - 4 - 2;

            $boxStartY = $y + 3;
            $maxHPermitido = $bottomDisponivel - $boxStartY;

            if ($maxHPermitido > 20 && $hmax > $maxHPermitido) {
                $hmax = $maxHPermitido;
            }
        }

        return parent::itens($x, $y, $nInicio, $hmax, $pag, $totpag, $hCabecItens);
    }

    /**
     * Enquadra perfeitamente o bloco de DADOS ADICIONAIS,
     * INFORMAÇÕES COMPLEMENTARES e RESERVADO AO FISCO sem cortes ou invasões de linhas.
     *
     * @param  float  $x
     * @param  float  $y
     * @param  float  $h
     * @return float
     */
    protected function dadosAdicionais($x, $y, $h)
    {
        $marginf = $this->marginf ?? 2;
        // Posiciona a caixa terminando exatamente acima do rodapé
        $yFimBoxes = $this->maxH - $marginf - 4;
        $yInicioBoxes = $yFimBoxes - $h;
        $yTitulo = $yInicioBoxes - 3.5;

        if ($this->orientacao === 'P') {
            $w = $this->wPrint;
        } else {
            $w = $this->wPrint - $this->wCanhoto;
        }

        // 1. TÍTULO DO GRUPO: DADOS ADICIONAIS
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => 'B'];
        $this->pdf->textBox($x, $yTitulo, $w, 3.5, 'DADOS ADICIONAIS', $aFont, 'T', 'L', 0, '');

        // 2. QUADRO ESQUERDO: INFORMAÇÕES COMPLEMENTARES
        $wAdic = $this->wAdic;
        $aFontHeader = ['font' => $this->fontePadrao, 'size' => 6, 'style' => 'B'];

        // Caixa externa com borda
        $this->pdf->textBox($x, $yInicioBoxes, $wAdic, $h, 'INFORMAÇÕES COMPLEMENTARES', $aFontHeader, 'T', 'L', 1, '');

        // Conteúdo textual interno (com margens internas para não colar na borda nem no título)
        $aFontText = ['font' => $this->fontePadrao, 'size' => $this->textadicfontsize * $this->pdf->k, 'style' => ''];
        $this->pdf->textBox($x + 1, $yInicioBoxes + 2.8, $wAdic - 2, $h - 3.2, $this->textoAdic, $aFontText, 'T', 'L', 0, '', false);

        // 3. QUADRO DIREITO: RESERVADO AO FISCO
        $xDir = $x + $wAdic;
        if ($this->orientacao === 'P') {
            $wDir = $this->wPrint - $wAdic;
        } else {
            $wDir = $this->wPrint - $wAdic - $this->wCanhoto;
        }

        $textoFisco = 'RESERVADO AO FISCO';
        if (isset($this->nfeProc) && $this->nfeProc->getElementsByTagName('xMsg')->length) {
            $textoFisco .= ' '.$this->nfeProc->getElementsByTagName('xMsg')->item(0)->nodeValue;
        }

        // Caixa externa com borda
        $this->pdf->textBox($xDir, $yInicioBoxes, $wDir, $h, $textoFisco, $aFontHeader, 'T', 'L', 1, '');

        // Texto de contingência (se houver)
        $xJust = $this->getTagValue($this->ide, 'xJust', 'Justificativa: ');
        $dhCont = $this->getTagValue($this->ide, 'dhCont', ' Entrada em contingência : ');
        $textoCont = '';
        switch ($this->tpEmis) {
            case 4:
                $textoCont = "CONTINGÊNCIA EPEC\n".$dhCont."\n".$xJust;
                break;
            case 5:
                $textoCont = "CONTINGÊNCIA FSDA\n".$dhCont."\n".$xJust;
                break;
            case 6:
                $textoCont = "CONTINGÊNCIA SVC-AN\n".$dhCont."\n".$xJust;
                break;
            case 7:
                $textoCont = "CONTINGÊNCIA SVC-RS\n".$dhCont."\n".$xJust;
                break;
        }

        if (! empty($textoCont)) {
            $aFontCont = ['font' => $this->fontePadrao, 'size' => 6, 'style' => ''];
            $this->pdf->textBox($xDir + 1, $yInicioBoxes + 2.8, $wDir - 2, $h - 3.2, $textoCont, $aFontCont, 'T', 'L', 0, '', false);
        }

        return $yFimBoxes;
    }
}
