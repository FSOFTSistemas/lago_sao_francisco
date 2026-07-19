<?php

namespace App\Http\Controllers;

use App\Models\Quarto;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RelatorioMovimentacaoController extends Controller
{
    public function index(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfWeek()->format('Y-m-d'));

        $dados = $this->buscarMovimentacaoPorPeriodo($dataInicio, $dataFim);

        return view('reserva.relatorio.movimentacao', array_merge($dados, [
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ]));
    }

    public function pdf(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfWeek()->format('Y-m-d'));

        $dados = $this->buscarMovimentacaoPorPeriodo($dataInicio, $dataFim);

        $pdf = Pdf::loadView('reserva.relatorio.movimentacao_pdf', array_merge($dados, [
            'dataInicio' => Carbon::parse($dataInicio)->format('d/m/Y'),
            'dataFim' => Carbon::parse($dataFim)->format('d/m/Y'),
        ]))->setPaper('a4', 'landscape');

        return $pdf->stream('previsao_movimentacao.pdf');
    }

    /**
     * Monta a grade dia a dia do período: para cada data, calcula saídas, entradas,
     * ocupação, crianças, café da manhã, reservas bloqueadas, disponibilidade e
     * percentual de ocupação — mais uma linha de totais/média ponderada do período.
     */
    private function buscarMovimentacaoPorPeriodo(string $dataInicio, string $dataFim): array
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->startOfDay();

        $quartosAtivos = Quarto::where('status', 1)->with('categoria')->get();
        $totalApto = $quartosAtivos->count();
        $totalPaxCapacidade = $quartosAtivos->sum(fn ($q) => $q->categoria->ocupantes ?? 0);

        // Carrega as reservas do período inteiro numa query só (padrão usado em
        // MapaController::getDadosMapa/calcularOcupacaoPorData), depois itera os dias em PHP.
        $reservas = Reserva::whereNotIn('situacao', ['cancelado'])
            ->where('data_checkin', '<=', $fim->format('Y-m-d'))
            ->where('data_checkout', '>', $inicio->format('Y-m-d'))
            ->get();

        $pax = fn ($r) => $r->n_adultos + $r->n_criancas + ($r->n_criancas_nao_pagantes ?? 0);

        $linhas = [];
        $totais = $this->linhaZerada();
        $totalDias = 0;

        $dataAtual = $inicio->copy();
        while ($dataAtual->lte($fim)) {
            $dataStr = $dataAtual->format('Y-m-d');

            $doDia = $reservas->filter(fn ($r) => $r->data_checkin <= $dataStr && $r->data_checkout > $dataStr);
            $normais = $doDia->whereIn('situacao', ['reserva', 'hospedado', 'finalizada']);
            $bloqueios = $doDia->where('situacao', 'bloqueado');

            $linha = $this->linhaZerada();
            $linha['data'] = $dataAtual->copy();

            $linha['saidas_apto'] = $normais->filter(fn ($r) => $r->data_checkout == $dataStr)->count();
            $linha['saidas_pax'] = $normais->filter(fn ($r) => $r->data_checkout == $dataStr)->sum($pax);

            $linha['entradas_apto'] = $normais->filter(fn ($r) => $r->data_checkin == $dataStr)->count();
            $linha['entradas_pax'] = $normais->filter(fn ($r) => $r->data_checkin == $dataStr)->sum($pax);

            $linha['ocupacao_apto'] = $normais->count();
            $linha['ocupacao_pax'] = $normais->sum($pax);

            $linha['criancas_pag'] = $normais->sum('n_criancas');
            $linha['criancas_free'] = $normais->sum('n_criancas_nao_pagantes');
            $linha['criancas_total'] = $linha['criancas_pag'] + $linha['criancas_free'];

            $linha['cafe_adl'] = $normais->sum('n_adultos');
            $linha['cafe_chd'] = $linha['criancas_total'];

            $linha['bloqueio_apto'] = $bloqueios->count();
            $linha['bloqueio_pax'] = $bloqueios->sum($pax);

            $linha['disp_apto'] = max(0, $totalApto - $linha['ocupacao_apto'] - $linha['bloqueio_apto']);

            $linha['pct_ocupacao_apto'] = $totalApto > 0 ? round($linha['ocupacao_apto'] / $totalApto * 100) : 0;
            $linha['pct_ocupacao_pax'] = $totalPaxCapacidade > 0 ? round($linha['ocupacao_pax'] / $totalPaxCapacidade * 100) : 0;

            foreach ($linha as $chave => $valor) {
                if ($chave === 'data' || str_starts_with($chave, 'pct_')) {
                    continue;
                }
                $totais[$chave] += $valor;
            }

            $linhas[] = $linha;
            $totalDias++;
            $dataAtual->addDay();
        }

        $totais['pct_ocupacao_apto'] = ($totalApto > 0 && $totalDias > 0)
            ? round($totais['ocupacao_apto'] / ($totalApto * $totalDias) * 100)
            : 0;
        $totais['pct_ocupacao_pax'] = ($totalPaxCapacidade > 0 && $totalDias > 0)
            ? round($totais['ocupacao_pax'] / ($totalPaxCapacidade * $totalDias) * 100)
            : 0;

        return [
            'linhas' => $linhas,
            'totais' => $totais,
            'totalApto' => $totalApto,
        ];
    }

    private function linhaZerada(): array
    {
        return [
            'saidas_apto' => 0, 'saidas_pax' => 0,
            'entradas_apto' => 0, 'entradas_pax' => 0,
            'ocupacao_apto' => 0, 'ocupacao_pax' => 0,
            'criancas_pag' => 0, 'criancas_free' => 0, 'criancas_total' => 0,
            'cafe_adl' => 0, 'cafe_chd' => 0,
            'bloqueio_apto' => 0, 'bloqueio_pax' => 0,
            'disp_apto' => 0,
            'pct_ocupacao_apto' => 0, 'pct_ocupacao_pax' => 0,
        ];
    }
}
