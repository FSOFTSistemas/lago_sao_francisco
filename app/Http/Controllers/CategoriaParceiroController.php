<?php

namespace App\Http\Controllers;

use App\Models\CategoriaParceiro;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoriaParceiroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = CategoriaParceiro::all();
        return view('categoriasParceiro.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'descricao' => 'string|required'
            ], [
                'descricao.required' => 'o campo descrição é obrigatório',
                'descricao.string' => 'o campo deve ser do tipo string'
            ]);

            CategoriaParceiro::create($validated);
            return redirect()->route('categoriasParceiro.index')->with('success', 'Categoria criada com sucesso!');

        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao criar categoria', $e);
        }
    }

    public function update(Request $request, $id)
    {
         try{
            $categoria = CategoriaParceiro::findOrFail($id);
            $request->validate([
                'descricao' => 'string|required'
            ], [
                'descricao.required' => 'o campo descrição é obrigatório',
                'descricao.string' => 'o campo deve ser do tipo string'
            ]);

            $categoria->update($request->all());
            return redirect()->route('categoriasParceiro.index')->with('success', 'Categoria atualizada com sucesso!');

        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao atualizar categoria', $e);
        }
    }

    public function destroy($id)
    {
        try{
            $categoria = CategoriaParceiro::findOrFail($id);
            $categoria->delete();
            return redirect()->route('categoriasParceiro.index')->with('success', 'Categoria excluída com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao excluir categoria', $e);
        }
    }
}
