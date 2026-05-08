<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioHospedesController extends Controller
{
    /**
     * Exibe a página inicial do relatório
     */
    public function index()
    {
        return view('reserva.relatorio.index');
    }

    /**
     * Filtra os hóspedes por período e exibe na tela
     */
    public function filtrar(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $hospedes = $this->getHospedesPorPeriodo($request->data_inicio, $request->data_fim);

        return view('reserva.relatorio.index', compact('hospedes'));
    }

    /**
     * Gera o PDF do relatório de hóspedes
     */
    public function gerarPdf(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $hospedes = $this->getHospedesPorPeriodo($request->data_inicio, $request->data_fim);

        $dataInicioFormatada = Carbon::parse($request->data_inicio)->format('d/m/Y');
        $dataFimFormatada = Carbon::parse($request->data_fim)->format('d/m/Y');

        $pdf = PDF::loadView('reserva.relatorio.hospedes_periodo_pdf', [
            'hospedes' => $hospedes,
            'data_inicio' => $dataInicioFormatada,
            'data_fim' => $dataFimFormatada
        ]);

        return $pdf->stream('relatorio_hospedes_' . date('YmdHis') . '.pdf');
    }

    /**
     * Método auxiliar para buscar as métricas de hóspedes e pets
     */
    private function getHospedesPorPeriodo($inicio, $fim)
    {
        // Definimos o início e fim do dia para garantir que a comparação inclua todo o período
        $dataInicio = Carbon::parse($inicio)->startOfDay();
        $dataFim = Carbon::parse($fim)->endOfDay();

        // Buscamos reservas que tiveram estadia dentro do período (interseção)
        $totais = DB::table('reservas')
            ->where('data_checkin', '<=', $dataFim)
            ->where('data_checkout', '>=', $dataInicio)
            ->select(
                DB::raw('SUM(n_adultos) as total_adultos'),
                DB::raw('SUM(n_criancas) as total_criancas'),
                DB::raw('SUM(n_criancas_nao_pagantes) as total_criancas_np')
            )
            ->first();

        // Soma da quantidade de Pets nas reservas filtradas acima
        $totalPets = DB::table('reserva_pets')
            ->join('reservas', 'reserva_pets.reserva_id', '=', 'reservas.id')
            ->where('reservas.data_checkin', '<=', $dataFim)
            ->where('reservas.data_checkout', '>=', $dataInicio)
            ->sum('reserva_pets.quantidade');

        // Retorna um objeto formatado e converte para inteiro para evitar erros
        return (object) [
            'adultos' => (int) ($totais->total_adultos ?? 0),
            'criancas' => (int) ($totais->total_criancas ?? 0),
            'criancas_np' => (int) ($totais->total_criancas_np ?? 0),
            'pets' => (int) ($totalPets ?? 0),
            'total_geral' => (int) (($totais->total_adultos ?? 0) + ($totais->total_criancas ?? 0) + ($totais->total_criancas_np ?? 0))
        ];
    }
}
