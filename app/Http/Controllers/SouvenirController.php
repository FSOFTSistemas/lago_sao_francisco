<?php

namespace App\Http\Controllers;

use App\Models\Souvenir;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SouvenirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $souvenirs = Souvenir::all();
        return view('souvenir.index', compact('souvenirs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'estoque' => 'required|numeric',
            ]);
            Souvenir::create($validated);
            return redirect()->route('souvenir.index')->with('success', 'Produto adicionado com sucesso!');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao adicionar produto!', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         try {
            $souvenir = Souvenir::findOrFail($id);
            $validated = $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'estoque' => 'required|numeric',
            ]);
            $souvenir->update($validated);
            return redirect()->route('souvenir.index')->with('success', 'Produto atualizado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao atualizar produto!', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
        $souvenir = Souvenir::findOrFail($id);
        $souvenir->delete();
        return redirect()->route('souvenir.index')->with('success', 'Produto removido com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao remover produto!', $e);
        }
    }
}
