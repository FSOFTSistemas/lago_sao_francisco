<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Caixa;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Master')) {
            // Dados de Day Use para gráfico de barras
            $inicioMes = Carbon::now()->startOfMonth();
            $fimMes = Carbon::now()->endOfMonth();

            $resultados = DB::table('mov_day_uses')
                ->join('day_uses', 'mov_day_uses.dayuse_id', '=', 'day_uses.id')
                ->join('itens_day_uses', 'mov_day_uses.item_dayuse_id', '=', 'itens_day_uses.id')
                ->select(
                    DB::raw('DATE(day_uses.data) as dia'),
                    DB::raw("SUM(CASE WHEN itens_day_uses.passeio = 1 THEN mov_day_uses.quantidade * itens_day_uses.valor ELSE 0 END) as passeio_valor"),
                    DB::raw("SUM(CASE WHEN itens_day_uses.passeio != 1 AND LOWER(itens_day_uses.descricao) LIKE '%entrada%' THEN mov_day_uses.quantidade * itens_day_uses.valor ELSE 0 END) as entrada_valor"),
                    DB::raw("SUM(CASE WHEN itens_day_uses.passeio = 1 THEN mov_day_uses.quantidade ELSE 0 END) as passeio_qtd"),
                    DB::raw("SUM(CASE WHEN itens_day_uses.passeio != 1 AND LOWER(itens_day_uses.descricao) LIKE '%entrada%' THEN mov_day_uses.quantidade ELSE 0 END) as entrada_qtd")
                )
                ->whereBetween('day_uses.data', [$inicioMes, $fimMes])
                ->groupBy(DB::raw('DATE(day_uses.data)'))
                ->orderBy('dia')
                ->get();

            // Preparar os arrays para o gráfico
            $labels = [];
            $passeioValor = [];
            $entradaValor = [];
            $passeioQtd = [];
            $entradaQtd = [];

            foreach ($resultados as $r) {
                $labels[] = Carbon::parse($r->dia)->format('d/m');
                $passeioValor[] = round($r->passeio_valor, 2);
                $entradaValor[] = round($r->entrada_valor, 2);
                $passeioQtd[] = $r->passeio_qtd;
                $entradaQtd[] = $r->entrada_qtd;
            }

            // Caixas disponíveis para filtro
            $caixas = Caixa::all();

            return view('home.master', compact(
                'labels',
                'passeioValor',
                'entradaValor',
                'passeioQtd',
                'entradaQtd',
                'caixas'
            ));
        }

        if ($user->hasRole('financeiro')) {
            return view('home.financeiro');
        }

        if ($user->hasRole('funcionario')) {
            return view('home.funcionario');
        }

        abort(403, 'Acesso não autorizado.');
    }

    public function graficoFluxoCaixa(Request $request)
{
    $usuario = Auth::user();
    $empresaId = session('empresa_id');

    $query = DB::table('fluxo_caixas')
        ->join('movimentos', 'fluxo_caixas.movimento_id', '=', 'movimentos.id')
        ->select('movimentos.descricao as nome', DB::raw('SUM(fluxo_caixas.valor) as total'));

    // Filtro por empresa
    if ($usuario->hasRole('Master') && $empresaId) {
        $query->where('fluxo_caixas.empresa_id', $empresaId);
    } elseif (!$usuario->hasRole('Master')) {
        $query->where('fluxo_caixas.empresa_id', $usuario->empresa_id);
    }

    // Filtro por data
    if ($request->modo_data === 'periodo' && $request->filled(['data_inicio', 'data_fim'])) {
        $query->whereBetween('fluxo_caixas.data', [$request->data_inicio, $request->data_fim]);
    } else {
        $query->whereDate('fluxo_caixas.data', Carbon::today());
    }

    // Filtro por caixa
    if ($request->filled('caixa_id')) {
        $query->where('fluxo_caixas.caixa_id', $request->caixa_id);
    }

    // Exclusões específicas
    $query->where(function ($sub) {
        $sub->whereRaw("LOWER(movimentos.descricao) NOT LIKE 'cancelamento-%'")
            ->whereNotIn('movimentos.descricao', [
                'fechamento de caixa',
                'abertura de caixa',
            ]);
    });

    $dados = $query
        ->groupBy('movimento_id', 'movimentos.descricao')
        ->get();

    return response()->json($dados);
}
}