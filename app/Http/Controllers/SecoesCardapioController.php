<?php

namespace App\Http\Controllers;

use App\Models\SecoesCardapio;
use Illuminate\Console\View\Components\Secret;
use Illuminate\Http\Request;

class SecoesCardapioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $secoesDoCardapio = SecoesCardapio::all();
        return view('secoesDoCardapio.index', compact('secoesDoCardapio'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome_secao_cardapio' => 'required|string',
                'opcao_conteudo_principal_refeicao' => 'required|boolean',
                'ordem_exibicao' => 'required|integer',
                'cardapio_id' => 'required|integer',
            ]);
            SecoesCardapio::create($request->all());
            return redirect()->route('secoesDoCardapio.index')->with('success', 'Seção criada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar seção do cardápio.');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SecoesCardapio $secoesCardapio)
    {
        try{
            $secoesCardapio = SecoesCardapio::findOrFail($secoesCardapio->id);
            $validated = $request->validate([
                'nome_secao_cardapio' => 'required|string',
                'opcao_conteudo_principal_refeicao' => 'required|boolean',
                'ordem_exibicao' => 'required|integer',
                'cardapio_id' => 'required|integer',
            ]);
            $secoesCardapio->update($validated);
            return redirect()->route('secoesDoCardapio.index')->with('success', 'Seção atualizada com sucesso.');

        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao atualizar seção do cardápio.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SecoesCardapio $secoesCardapio)
    {
        try{
            $secoesCardapio = SecoesCardapio::findOrFail($secoesCardapio->id);
            $secoesCardapio->delete();
            return redirect()->route('secoesDoCardapio.index')->with('success', 'Seção Deletada com sucesso');
        } catch(\Exception $e) {
            return redirect()->back()->with('error', 'erro ao excluir seção do cardápio');
        }
    }
}
