<?php

namespace App\Http\Controllers;

use App\Models\ReservaItem;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RelatorioProdutosController extends Controller
{
    /**
     * Exibe a página inicial do relatório
     */
    public function index()
    {
        return view('reserva.relatorio.index');
    }

    /**
     * Filtra os produtos por período e exibe o relatório
     */
    public function filtrar(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;

        // Buscar produtos vendidos no período, agrupados por produto
        $produtos = $this->getProdutosPorPeriodo($dataInicio, $dataFim);
        
        // Calcular o total geral
        $total_geral = $produtos->sum('total');

        return view('reserva.relatorio.index', compact('produtos', 'total_geral'));
    }

    /**
     * Gera o PDF do relatório
     */
    public function gerarPdf(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;

        // Buscar produtos vendidos no período, agrupados por produto
        $produtos = $this->getProdutosPorPeriodo($dataInicio, $dataFim);
        
        // Calcular o total geral
        $total_geral = $produtos->sum('total');

        // Formatar as datas para exibição
        $dataInicioFormatada = \Carbon\Carbon::parse($dataInicio)->format('d/m/Y');
        $dataFimFormatada = \Carbon\Carbon::parse($dataFim)->format('d/m/Y');

        // Gerar o PDF
        $pdf = PDF::loadView('reserva.relatorio.produtos_periodo_pdf', [
            'produtos' => $produtos,
            'total_geral' => $total_geral,
            'data_inicio' => $dataInicioFormatada,
            'data_fim' => $dataFimFormatada
        ]);

        // Definir o nome do arquivo
        $nomeArquivo = 'relatorio_produtos_' . date('YmdHis') . '.pdf';

        // Retornar o PDF para download ou visualização
        return $pdf->stream($nomeArquivo);
    }

    /**
     * Método auxiliar para buscar produtos por período
     */
    private function getProdutosPorPeriodo($dataInicio, $dataFim)
{
    $dataInicio = Carbon::parse($dataInicio);
    $dataFim = Carbon::parse($dataFim)->addDay();

    return DB::table('reserva_items')
        ->join('reservas', 'reserva_items.reserva_id', '=', 'reservas.id')
        ->join('produtos', 'reserva_items.produto_id', '=', 'produtos.id')
        ->whereBetween('reservas.data_checkout', [$dataInicio->toDateString(), $dataFim->toDateString()])
        ->select(
            'produtos.id',
            'produtos.descricao',
            'produtos.preco_venda',
            DB::raw('SUM(reserva_items.quantidade) AS quantidade_total'),
            DB::raw('SUM(reserva_items.quantidade * produtos.preco_venda) AS total')
        )
        ->groupBy('produtos.id', 'produtos.descricao', 'produtos.preco_venda')
        ->orderBy('produtos.descricao')
        ->get();
}

}
