<?php

namespace App\Http\Controllers;

use App\Models\CategoriasDeItensCardapio;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use View;

class CategoriasDeItensCardapioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = CategoriasDeItensCardapio::all();
        return view('categoriaItensCardapio.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      try {
            $validated = $request->validate([
                'sessao_cardapio_id' => 'required|exists:secoes_cardapios,id',
                'refeicao_principal_id' => 'required|exists:refeicao_principals,id',
                'nome_categoria_item' => 'required|string|max:255',
                'numero_escolhas_permitidas' => 'required|integer',
                'eh_grupo_escolha_exclusiva' => 'required|boolean',
                'ordem_exibicao' => 'required|integer',
            ]);

            $categoria = CategoriasDeItensCardapio::create($validated);

            return redirect()->route('categoriaItensCardapio.index')->with('sucess', 'Categoria de itens do cardapio criado com sucesso');
        } catch (Exception $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Erro ao criar categoria de itens do cardapio');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CategoriasDeItensCardapio $categoriasDeItensCardapio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoriasDeItensCardapio $categoriasDeItensCardapio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriasDeItensCardapio::findOrFail($id);

            $validated = $request->validate([
                'sessao_cardapio_id' => 'sometimes|required|exists:secoes_cardapios,id',
                'refeicao_principal_id' => 'sometimes|required|exists:refeicao_principals,id',
                'nome_categoria_item' => 'sometimes|required|string|max:255',
                'numero_escolhas_permitidas' => 'sometimes|required|integer',
                'eh_grupo_escolha_exclusiva' => 'sometimes|required|boolean',
                'ordem_exibicao' => 'sometimes|required|integer',
            ]);

            $categoria->update($validated);

            return redirect()->route('categoriaItensCardapio.index')->with('sucess', 'Categoria de itens do cardapio atualizado com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Categoria de itens do cardapio não existe');
        } catch (Exception $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Erro ao atualizar categoria de itens do cardapio');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       try {
            $categoria = CategoriasDeItensCardapio::findOrFail($id);
            $categoria->delete();

            return redirect()->route('categoriaItensCardapio.index')->with('sucess', 'Categoria de itens do cardapio deletado com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Categoria de itens do cardapio não existe');
        } catch (Exception $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Erro ao deletar categoria de itens do cardapio');
        }
    }
}
