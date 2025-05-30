<?php

namespace App\Http\Controllers;

use App\Models\ItensDoCardapio;
use Illuminate\Http\Request;

class ItensDoCardapioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itens = ItensDoCardapio::all();
        return view('itemCardapio.index', compact('itens'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'nome_item' => 'string|required',
                'tipo_item' => 'string|required'
            ]);
            ItensDoCardapio::create($request->all());
            return redirect()->route('itemCardapio.index')->with('success', 'Item Cadastrado com Sucesso');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao Cadastrar Item');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItensDoCardapio $itensDoCardapio)
    {
        try{
            $itensDoCardapio = ItensDoCardapio::findOrFail($itensDoCardapio->id);
            $validated = $request->validate([
                'nome_item' => 'string|required',
                'tipo_item' => 'string|required'
            ]);
            $itensDoCardapio->update($validated);
            return redirect()->route('itemCardapio.index')->with('success', 'Item Atualizado com Sucesso');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao Atualizar Item');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItensDoCardapio $itensDoCardapio)
    {
        try{
            $itensDoCardapio = ItensDoCardapio::finOrFail($itensDoCardapio->id);
            $itensDoCardapio->delete();
            return redirect()->route('itemCardapio.index')->with('success', 'Item deletado com sucesso');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao deletar item');
        }
    }
}
