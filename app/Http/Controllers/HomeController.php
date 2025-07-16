<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Caixa;
use App\Models\DayUse;
use App\Models\MovDayUse;

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
        $empresaId = $user->empresa_id;

        // Filtros do formulário
        $dataInicio = $request->input('data_inicio', now()->toDateString());
        $dataFim = $request->input('data_fim', now()->toDateString());

        // Validação básica de intervalo
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            if (Carbon::parse($dataFim)->lt(Carbon::parse($dataInicio))) {
                return redirect()->route('home.index')
                    ->with('error', 'A data final não pode ser anterior à data inicial.');
            }
        }

        // Listagem dos DayUses no intervalo
        $dayuses = DayUse::with([
            'cliente',
            'vendedor',
            'itens.item',
            'formaPag.formaPagamento',
            'souvenirs'
        ])
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->orderByDesc('data')
            ->get()
            ->map(function ($dayuse) {
                $valorPago = $dayuse->formaPag->sum('valor') ?? 0;
                $valorSouvenirs = $dayuse->souvenirs->sum(function ($souvenir) {
                    return ($souvenir->valor ?? 0) * ($souvenir->pivot->quantidade ?? 0);
                });

                $valorLiquido = ($dayuse->total ?? 0)
                    + ($dayuse->acrescimo ?? 0)
                    - ($dayuse->desconto ?? 0)
                    + $valorSouvenirs;

                $dayuse->saldo = $valorLiquido - $valorPago;
                return $dayuse;
            });

        // Card resumo por item
        $movimentos = MovDayUse::with('item')
    ->whereHas('dayuse', function ($q) use ($dataInicio, $dataFim) {
        $q->whereBetween('data', [$dataInicio, $dataFim]);
    })
    ->select('item_dayuse_id', DB::raw('SUM(quantidade) as total_quantidade'))
    ->groupBy('item_dayuse_id')
    ->get()
    ->map(function ($mov) {
        $mov->item_nome = $mov->item->descricao ?? 'Item';
        $mov->passeio = $mov->item->passeio ?? false;
        
        // Calcular valor total do item movimentado
        $valorUnitario = $mov->item->valor ?? 0;
        $mov->valor_total = $valorUnitario * $mov->total_quantidade;

        return $mov;
    });

        // Gráfico por dia do mês atual
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

        // Gráfico por item e dia
        $movimentosPorDia = MovDayUse::with('item', 'dayuse')
            ->whereHas('dayuse', function ($q) use ($inicioMes, $fimMes) {
                $q->whereBetween('data', [$inicioMes, $fimMes]);
            })
            ->get()
            ->groupBy(fn($mov) => Carbon::parse($mov->dayuse->data)->format('Y-m-d'));

        $labelsGrafico = $movimentosPorDia->keys()->sort()->values()->toArray();
        $itens = [];
        $tiposItens = [];

        foreach ($movimentosPorDia as $data => $movs) {
            foreach ($movs as $mov) {
                $nome = $mov->item->descricao ?? 'Desconhecido';
                $tiposItens[$nome] = $mov->item->passeio ?? false;
                $itens[$nome][$data] = ($itens[$nome][$data] ?? 0) + $mov->quantidade;
            }
        }

        foreach ($itens as $nome => $datas) {
            foreach ($labelsGrafico as $label) {
                if (!isset($itens[$nome][$label])) {
                    $itens[$nome][$label] = 0;
                }
            }
            ksort($itens[$nome]);
        }

        $dadosGrafico = [];
        foreach ($itens as $nome => $datas) {
            $dadosGrafico[] = [
                'nome' => $nome,
                'data' => array_values($datas),
            ];
        }

        // Caixas para filtro
        $caixas = Caixa::all();

        return view('home.master', compact(
            'dayuses',
            'dataInicio',
            'dataFim',
            'movimentos',
            'dadosGrafico',
            'labelsGrafico',
            'tiposItens',
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