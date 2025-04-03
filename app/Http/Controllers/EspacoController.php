<?php

namespace App\Http\Controllers;

use App\Models\Espaco;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EspacoController extends Controller
{
    public function index()
    {
        $espacos = Espaco::all();
        $empresas = Empresa::all();
        return view('espacos.index', compact('espacos', 'empresas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required',
                'status' => 'required|in:disponivel,alugado',
                'valor' => 'required',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            Espaco::create($request->all());
            return redirect()->route('espaco.index')->with('success', 'Espaço criado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar o Espaço');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Espaco $espaco)
    {
        try {
            $espaco = Espaco::findOrFail($espaco->id);
            $request->validate([
                'nome' => 'required',
                'status' => 'required|in:disponivel,alugado',
                'valor' => 'required',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $espaco->update($request->all());
            return redirect()->route('espaco.index')->with('success', 'Espaço atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o Espaço');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Espaco $espaco)
    {
        try {
            $espaco = Espaco::findOrFail($espaco->id);
            $espaco->delete();
            return redirect()->route('espaco.index')->with('success', 'Espaço deletado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar o Espaço');
        }
    }
}
