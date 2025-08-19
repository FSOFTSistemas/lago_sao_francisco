<?php

namespace App\Http\Controllers;

use App\Models\LogReserva;
use App\Models\ReservaItem;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservaItemController extends Controller
{
    public function index()
    {
        $itens = ReservaItem::with(['produto', 'reserva'])->latest()->paginate(10);
        return view('reserva-item.index', compact('itens'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'reserva_id' => 'required|exists:reservas,id',
                'itens' => 'required|array|min:1',
                'itens.*.produto_id' => 'required|exists:produtos,id',
                'itens.*.quantidade' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $itensCriados = [];

            foreach ($request->itens as $itemData) {
                $item = ReservaItem::create([
                    'produto_id' => $itemData['produto_id'],
                    'reserva_id' => $request->reserva_id,
                    'quantidade' => $itemData['quantidade'],
                ]);
                LogReserva::registrarProdutoAdicionado($request->reserva_id, Auth::id(), $item);

                // Carregar o produto para retornar na resposta
                $item->load('produto');
                $itensCriados[] = $item;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produtos adicionados à reserva com sucesso!',
                'itens' => $itensCriados
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(ReservaItem $reservaItem)
    {
        $reservaItem->load(['produto', 'reserva']);
        return view('reserva-item.show', compact('reservaItem'));
    }

    public function update(Request $request, ReservaItem $reservaItem)
    {
        try {
            $request->validate([
                'quantidade' => 'required|integer|min:1',
            ]);

            $reservaItem->update($request->only(['quantidade']));

            return response()->json([
                'success' => true,
                'message' => 'Item atualizado com sucesso!',
                'item' => $reservaItem->load('produto')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = ReservaItem::findOrFail($id);
            $reservaId = $item->reserva->id;
            LogReserva::registrarProdutoRemovido($reservaId, Auth::id(), $item);

            $item->delete();


            return response()->json([
                'success' => true,
                'message' => 'Produto removido da reserva com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover produto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByReserva($reservaId)
    {
        try {
            $itens = ReservaItem::where('reserva_id', $reservaId)
                ->with('produto')
                ->get();

            return response()->json([
                'success' => true,
                'itens' => $itens
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar itens: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTotalByReserva($reservaId)
    {
        try {
            $total = ReservaItem::where('reserva_id', $reservaId)
                ->with('produto')
                ->get()
                ->sum(function ($item) {
                    return $item->quantidade * $item->produto->preco_venda;
                });

            return response()->json([
                'success' => true,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular total: ' . $e->getMessage()
            ], 500);
        }
    }
}
