<?php

namespace App\Http\Controllers;

use App\Models\BuffetItem;
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
        return view('buffet.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        BuffetItem::create([
            'nome' => $request->nome,
            'valor_unitario' => $request->valor_unitario,
        ]);

        return redirect()->route('buffet.index')->with('success', 'Item de buffet cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $item = BuffetItem::find($id);
        return view('buffet.create', compact('item'));
    }

    public function update(Request $request, BuffetItem $buffet)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_unitario' => 'required|numeric|min:0',
        ]);

        $buffet->update($request->only('nome', 'valor_por_pessoa'));

        return redirect()->route('buffet.index')->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(BuffetItem $buffet)
    {
        $buffet->delete();
        return redirect()->route('buffet.index')->with('success', 'Item excluído com sucesso!');
    }
}
