<?php

namespace App\Http\Controllers;

use App\Models\VendaItem;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;

class VendaItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendas = Venda::all();
        $produtos = Produto::all();
        $vendaItems = VendaItem::all();
        return view('venda_item.index', compact('vendas', 'produtos', 'vendaItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'venda_id' => 'required|exists:vendas,id',
                'quantidade' => 'required|integer|min:1',
                'valor_unitario' => 'required|numeric',
                'subtotal' => 'required|numeric',
                'acrescimo' => 'nullable|numeric',
                'deconto' => 'nullable|numeric',
                'total' => 'required|numeric',
            ]);

            VendaItem::create($request->all());
            return redirect()->route('venda_item.index')->with('success', 'Item de venda criado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendaItem $vendaItem)
    {
        try {
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'venda_id' => 'required|exists:vendas,id',
                'quantidade' => 'required|integer|min:1',
                'valor_unitario' => 'required|numeric',
                'subtotal' => 'required|numeric',
                'acrescimo' => 'nullable|numeric',
                'deconto' => 'nullable|numeric',
                'total' => 'required|numeric',
            ]);

            $vendaItem->update($request->all());
            return redirect()->route('venda_item.index')->with('success', 'Item de venda atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendaItem $vendaItem)
    {
        try {
            $vendaItem->delete();
            return redirect()->route('venda_item.index')->with('success', 'Item de venda deletado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar item de venda');
        }
    }
}
