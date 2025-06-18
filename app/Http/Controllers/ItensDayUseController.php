<?php

namespace App\Http\Controllers;

use App\Models\ItensDayUse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItensDayUseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $itens = ItensDayUse::all();
        return view('itensDayuse.index', compact('itens'));
    }


    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'descricao' => 'string|required',
                'valor' => 'numeric|required',
                'passeio' => 'boolean'
            ], [
                'descricao.required' => 'O campo descrição é obrigatório.',
                'valor.required' => 'O campo valor é obrigatório.',
                'valor.numeric' => 'O campo valor deve ser um número.',
                
            ]);
            ItensDayUse::create($validated);
            return redirect()->route('itemDayuse.index')->with('success', 'Entrada/Passeio criado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao criar Entrada/Passeio', $e);
        }
    }


    public function update(Request $request, ItensDayUse $itensDayUse)
    {
        try{
            $itens = ItensDayUse::findOrFail($itensDayUse->id);
            $request->validate([
                'descricao' => 'string|required',
                'valor' => 'numeric|required',
                'passeio' => 'boolean'
            ], [
                'descricao.required' => 'O campo descrição é obrigatório.',
                'valor.required' => 'O campo valor é obrigatório.',
                'valor.numeric' => 'O campo valor deve ser um número.',
                
            ]);
            $itens->update($request->all());
            return redirect()->route('itemDayuse.index')->with('success', 'Entrada/Passeio atualizado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao atualizar Entrada/Passeio', $e);
        }
    }

    public function destroy($id)
    {
        try{
            $itens = ItensDayUse::findOrFail($id);
            $itens->delete();
            return redirect()->route('itemDayuse.index')->with('success', 'Entrada/Passeio deletado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao deletar Entrada/Passeio', $e);
        }
    }
}
