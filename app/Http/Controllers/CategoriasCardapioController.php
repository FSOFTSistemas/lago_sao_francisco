<?php

namespace App\Http\Controllers;

use App\Models\CategoriasCardapio;
use Illuminate\Http\Request;

class CategoriasCardapioController extends Controller
{
    public function index()
    {
        $categorias = CategoriasCardapio::all();
        return view('categoriasCardapio.index', compact('categorias'));
    }

    public function create()
    {
        return view('categoriasCardapio.create');
    }

    public function store(Request $request)
    {
        CategoriasCardapio::create($request->only('nome'));
        return redirect()->route('categoriasCardapio.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(CategoriasCardapio $categoriasCardapio)
    {
        return view('categoriasCardapio.edit', compact('categoriaCardapio'));
    }


    public function update(Request $request, CategoriasCardapio $categoriasCardapio)
    {
        $categoriasCardapio->update($request->only('nome'));
        return redirect()->route('categoriasCardapio.index')->with('success', 'Categoria atualizada!');
    }

    public function destroy(CategoriasCardapio $categoriasCardapio)
    {
        $categoriasCardapio->delete();
        return redirect()->route('categoriasCardapio.index')->with('success', 'Categoria removida!');
    }
}

