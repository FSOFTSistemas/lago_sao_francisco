<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\ReservaItem;
use App\Models\Transacao;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VendaController extends Controller
{
    public function index()
    {
        $vendas = Venda::with(['cliente', 'formaPagamento', 'vendaItens.produto'])
            ->latest()
            ->paginate(10);
        
        return view('venda.index', compact('vendas'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'reserva_id' => 'required|exists:reservas,id',
                'itens' => 'required|array|min:1',
                'itens.*.produto_id' => 'required|exists:produtos,id',
                'itens.*.quantidade' => 'required|integer|min:1',
                'itens.*.valor_unitario' => 'required|numeric|min:0',
                'itens.*.total' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Calcular totais
            $subtotal = 0;
            $totalGeral = 0;
            
            foreach ($request->itens as $item) {
                $subtotal += $item['total'];
                $totalGeral += $item['total'];
            }

            // Criar a venda
            $venda = Venda::create([
                'forma_pagamento_id' => 1, // Padrão - você pode ajustar conforme necessário
                'empresa_id' => 1, // Padrão - você pode ajustar conforme necessário
                'data' => now(),
                'cliente_id' => null, // Será preenchido com o hóspede da reserva se necessário
                'usuario_id' => Auth::id() ?? 1,
                'observacao' => 'Venda de produtos para reserva #' . $request->reserva_id,
                'total' => $totalGeral,
                'subtotal' => $subtotal,
                'desconto' => 0,
                'acrescimo' => 0,
                'situacao' => 'finalizada',
                'gerado_nf' => false,
            ]);

            // Criar os itens da venda
            foreach ($request->itens as $item) {
                VendaItem::create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $item['total'],
                    'acrescimo' => 0,
                    'desconto' => 0,
                    'total' => $item['total'],
                    'venda_id' => $venda->id,
                ]);

                // Criar item na tabela ReservaItem
                ReservaItem::create([
                    'produto_id' => $item['produto_id'],
                    'reserva_id' => $request->reserva_id,
                    'quantidade' => $item['quantidade'],
                ]);
            }

            // Criar transação de produtos para aparecer no resumo da reserva
            $transacao = Transacao::create([
                'descricao' => 'Venda de produtos - Venda #' . $venda->id,
                'status' => true,
                'forma_pagamento_id' => 1, // Padrão
                'categoria' => 'produtos',
                'data_pagamento' => now()->format('Y-m-d'),
                'data_vencimento' => null,
                'tipo' => 'pagamento',
                'valor' => $totalGeral,
                'observacoes' => 'Produtos adicionados à reserva',
                'reserva_id' => $request->reserva_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produtos adicionados com sucesso!',
                'venda' => $venda->load(['vendaItens.produto']),
                'transacaoVenda' => $transacao->load('formaPagamento')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Venda $venda)
    {
        $venda->load(['cliente', 'formaPagamento', 'vendaItens.produto']);
        return view('venda.show', compact('venda'));
    }

    public function update(Request $request, Venda $venda)
    {
        try {
            $request->validate([
                'situacao' => 'required|in:pendente,finalizada,cancelada',
                'observacao' => 'nullable|string',
            ]);

            $venda->update($request->only(['situacao', 'observacao']));

            return response()->json([
                'success' => true,
                'message' => 'Venda atualizada com sucesso!',
                'venda' => $venda
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar venda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Venda $venda)
    {
        try {
            DB::beginTransaction();

            // Remover itens da reserva relacionados
            $vendaItens = $venda->vendaItens;
            foreach ($vendaItens as $item) {
                ReservaItem::where('produto_id', $item->produto_id)
                    ->where('quantidade', $item->quantidade)
                    ->delete();
            }

            // Remover transação relacionada
            Transacao::where('observacoes', 'like', '%Venda #' . $venda->id . '%')->delete();

            // Remover venda e seus itens
            $venda->vendaItens()->delete();
            $venda->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venda removida com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover venda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByReserva($reservaId)
    {
        try {
            $vendas = Venda::whereHas('vendaItens.reservaItens', function($query) use ($reservaId) {
                $query->where('reserva_id', $reservaId);
            })
            ->with(['vendaItens.produto', 'formaPagamento'])
            ->get();

            return response()->json([
                'success' => true,
                'vendas' => $vendas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar vendas: ' . $e->getMessage()
            ], 500);
        }
    }
}