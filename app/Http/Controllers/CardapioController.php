<?php

namespace App\Http\Controllers;

use App\Models\Cardapio;
use App\Models\CategoriasCardapio;
use Illuminate\Http\Request;

class CardapioController extends Controller
{
    public function index()
    {
        $cardapios = Cardapio::with('categorias')->get();
        return view('cardapios.index', compact('cardapios'));
    }

    public function create()
    {
        $categorias = CategoriasCardapio::all();
        return view('cardapios.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $cardapio = Cardapio::create($request->only('nome', 'observacoes'));

        foreach ($request->categorias as $categoriaId => $quantidade) {
            if ($quantidade > 0) {
                $cardapio->categorias()->attach($categoriaId, ['quantidade_itens' => $quantidade]);
            }
        }

        return redirect()->route('cardapios.index')->with('success', 'Cardápio criado com sucesso!');
    }

    public function edit(Cardapio $cardapio)
    {   
        $cardapio = Cardapio::findOrFail($cardapio->id);
        $categorias = CategoriasCardapio::all();
        $selecionadas = $cardapio->categorias->pluck('pivot.quantidade_itens', 'id')->toArray();
        return view('cardapios.create', compact('cardapio', 'categorias', 'selecionadas'));
    }

    public function update(Request $request, Cardapio $cardapio)
    {
        $cardapio->update($request->only('nome', 'observacoes'));
        $cardapio->categorias()->detach();

        foreach ($request->categorias as $categoriaId => $quantidade) {
            if ($quantidade > 0) {
                $cardapio->categorias()->attach($categoriaId, ['quantidade_itens' => $quantidade]);
            }
        }

        return redirect()->route('cardapios.index')->with('success', 'Cardápio atualizado!');
    }

    public function destroy(Cardapio $cardapio)
    {
        $cardapio->delete();
        return redirect()->route('cardapios.index')->with('success', 'Cardápio removido!');
    }
}

