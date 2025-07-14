<?php

namespace App\Http\Controllers;

use App\Models\DayUse;
use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\DayUsePag;
use App\Models\FluxoCaixa;
use App\Models\Funcionario;
use App\Models\LogDayuse;
use App\Models\MovDayUse;
use App\Models\Movimento;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DayUseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->deletarDayuseSemPag();
        $empresaId = Auth::user()->empresa_id;

        // Datas padrão: hoje
        $dataInicio = $request->input('data_inicio', now()->toDateString());
        $dataFim = $request->input('data_fim', now()->toDateString());

        // Validação: data fim não pode ser anterior à início
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            if (Carbon::parse($dataFim)->lt(Carbon::parse($dataInicio))) {
                return redirect()->route('dayuse.index')
                    ->with('error', 'A data final não pode ser anterior à data inicial.');
            }
        }

        // Recuperar DayUses do período
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



        // Agrupar MovDayUse por item para contagem dos cards
        $movimentos = MovDayUse::with('item')
            ->whereHas('dayuse', function ($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('data', [$dataInicio, $dataFim]);
            })
            ->select('item_dayuse_id', DB::raw('SUM(quantidade) as total_quantidade'))
            ->groupBy('item_dayuse_id')
            ->get()
            ->map(function ($mov) {
                $mov->item_nome = $mov->item->descricao ?? 'Item';
                $mov->passeio = $mov->item->passeio ?? false;
                return $mov;
            });

        // Gráfico: agrupamento por item e data
        $inicioMesAtual = now()->startOfMonth()->toDateString();
        $fimMesAtual = now()->endOfMonth()->toDateString();

        $movimentosPorDia = MovDayUse::with('item', 'dayuse')
            ->whereHas('dayuse', function ($query) use ($inicioMesAtual,  $fimMesAtual) {
                $query->whereBetween('data', [$inicioMesAtual,  $fimMesAtual]);
            })
            ->get()
            ->groupBy(function ($mov) {
                return Carbon::parse($mov->dayuse->data)->format('Y-m-d');
            });

        $labels = $movimentosPorDia->keys()->sort()->values()->toArray();

        $itens = [];
        $tiposItens = [];

        foreach ($movimentosPorDia as $data => $movs) {
            foreach ($movs as $mov) {
                $nome = $mov->item->descricao ?? 'Desconhecido';
                $tiposItens[$nome] = $mov->item->passeio ?? false;
                $itens[$nome][$data] = ($itens[$nome][$data] ?? 0) + $mov->quantidade;
            }
        }

        // Preenche dias ausentes com zero
        foreach ($itens as $nome => $datas) {
            foreach ($labels as $dataLabel) {
                if (!isset($itens[$nome][$dataLabel])) {
                    $itens[$nome][$dataLabel] = 0;
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

        return view('dayuse.index', compact(
            'dayuses',
            'dataInicio',
            'dataFim',
            'movimentos',
            'dadosGrafico',
            'labels',
            'tiposItens'
        ));
    }


    public function deletarDayuseSemPag()
    {
        try {
            $dataHoje = now()->toDateString();

            $dayuses = DayUse::whereDate('created_at', $dataHoje)->get();
            foreach ($dayuses as $dayuse) {
                $temMov = MovDayUse::where('dayuse_id', $dayuse->id)->exists();
                $temPag = DayUsePag::where('dayuse_id', $dayuse->id)->exists();
                if ($temMov && !$temPag) {
                    $dayuse->delete();
                }
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dayuse._form');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return view('dayuse.show', [
            'id' => $id,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DayUse $dayUse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DayUse $dayUse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $dayuse = DayUse::findOrFail($id);
            $dayuse->delete();
            return redirect()->route('dayuse.index')->with('success', 'Day Use deletado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar Day Use!', $e);
        }
    }

    public function verificaSupervisor(Request $request)
    {
        $request->validate([
            'dayuse_id' => 'required|exists:day_uses,id',
            'senha' => 'required|string',
        ]);

        $funcionarios = Funcionario::whereNotNull('senha_supervisor')->get();

        foreach ($funcionarios as $func) {
            if (Hash::check($request->senha, $func->senha_supervisor)) {

                $dayUse = DayUse::with('formaPag')->findOrFail($request->dayuse_id);

                // Log da exclusão
                LogDayuse::create([
                    'usuario' => Auth::user()->name,
                    'supervisor' => $func->nome,
                    'acao' => 'Exclusão de DayUse',
                    'data_hora' => now(),
                    'observacao' => 'DayUse #' . $dayUse->id . ' excluído.',
                ]);

                // Buscar caixa aberto da empresa
                $caixa = Caixa::where('empresa_id', Auth::user()->empresa_id)
                    ->where('status', 'aberto')
                    ->where('usuario_id', Auth::id())
                    ->first();

                if ($caixa) {
                    foreach ($dayUse->formaPag as $pagamento) {
                        $descricao = strtolower($pagamento->formaPagamento->descricao); // Ex: "Cartão Crédito"
                        $slug = str_replace([' ', '_'], '-', $descricao); // "cartão-crédito"
                        $movimento = Movimento::where('descricao', 'cancelamento-' . $slug)->first();

                        if ($movimento) {
                            FluxoCaixa::create([
                                'descricao' => 'Cancelamento DayUse #' . $dayUse->id,
                                'valor' => -$pagamento->valor,
                                'valor_total' => -$pagamento->valor,
                                'tipo' => 'cancelamento',
                                'caixa_id' => $caixa->id,
                                'usuario_id' => Auth::id(),
                                'empresa_id' => Auth::user()->empresa_id,
                                'data' => now(),
                                'movimento_id' => $movimento->id,
                                'plano_de_conta_id' => 1,
                            ]);
                        }
                    }
                }

                // Exclui o DayUse
                $dayUse->delete();

                return response()->json(['message' => 'DayUse excluído com sucesso!']);
            }
        }

        return response()->json(['message' => 'Senha de supervisor inválida.'], 403);
    }
}
