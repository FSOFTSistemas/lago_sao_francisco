<?php

namespace App\Http\Controllers;

use App\Models\Adicional;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdicionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adicionais = Adicional::all();
        return view('adicionais.index', compact('adicionais'));
    }

 
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'descricao' => 'string|required',
                'valor' => 'numeric|required',
                'quantidade' => 'numeric|required'
            ], [
                'descricao.required' => 'O campo descricao é obrigatório',
                'valor.required' => 'O campo valor é obrigatório',
                'quantidade.required' => 'O campo quantidade é obrigatório'
            ]);

            Adicional::create($validated);
            return redirect()->route('adicionais.index')->with('success', 'Adicional criado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao criar Adicional', $e);

        }
    }

    public function update(Request $request, Adicional $adicional)
    {
        try{
            $adicional = Adicional::findOrFail($adicional->id);
             $validated = $request->validate([
                'descricao' => 'string|required',
                'valor' => 'numeric|required',
                'quantidade' => 'numeric|required'
            ], [
                'descricao.required' => 'O campo descricao é obrigatório',
                'valor.required' => 'O campo valor é obrigatório',
                'quantidade.required' => 'O campo quantidade é obrigatório'
            ]);
            $adicional->update($validated);
            return redirect()->route('adicionais.index')->with('success', 'Adicional atualizado com sucesso!');
        } catch(\Exception $e){
            return redirect()->back()->with('error', 'Erro ao atualizar Adicional', $e);
        }
    }

    public function destroy($id)
    {
        try {
            $adicional = Adicional::findOrFail($id);
            $adicional->delete();
            return redirect()->route('adicionais.index')->with('success', 'Adicional deletado com sucesso!');
        } catch(\Exception)
        {
            return redirect()->back()->with('error', 'Erro ao deletar adicional');
        }
    }
}
