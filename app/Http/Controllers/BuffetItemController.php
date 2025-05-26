<?php

namespace App\Http\Controllers;

use App\Models\BuffetItem;
use App\Models\CategoriasCardapio;
use Illuminate\Http\Request;

class BuffetItemController extends Controller
{
    public function index()
    {
        $itens = BuffetItem::all();
        return view('buffet.index', compact('itens'));
    }

    public function create()
    {
        $categorias = CategoriasCardapio::all();
        return view('buffet.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias_cardapio,id',
        ]);

        BuffetItem::create($request->all());

        return redirect()->route('buffet.index')->with('success', 'Item de buffet cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $item = BuffetItem::find($id);
        $categorias = CategoriasCardapio::all();
        return view('buffet.create', compact('item', 'categorias'));
    }

    public function update(Request $request, BuffetItem $buffet)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias_cardapio,id',
        ]);

        $buffet->update($request->all());

        return redirect()->route('buffet.index')->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(BuffetItem $buffet)
    {
        $buffet->delete();
        return redirect()->route('buffet.index')->with('success', 'Item excluído com sucesso!');
    }
}
