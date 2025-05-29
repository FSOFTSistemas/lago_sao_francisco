<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParceiroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parceiros = Parceiro::all();
        return view('parceiros.index', compact('parceiros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'string|required',
                'valor' => 'required',
                'categoria' => 'string|required'
            ]);
            Parceiro::create($request->all());
            return redirect()->route('parceiros.index')->with('success', 'Parceiro criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'erro ao criar Parceiro');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Parceiro $parceiro)
    {
        try {
            $parceiro = Parceiro::findOrFail($parceiro->id);
            $request->validate([
                'descricao' => 'string|required',
                'valor' => 'required',
                'categoria' => 'string|required'
            ]);
            $parceiro->update($request->all());
            return redirect()->route('parceiros.index')->with('success', 'Parceiro atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'erro ao atualizar Parceiro');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Parceiro $parceiro)
    {
        try {
            $parceiro = Parceiro::findOrFail($parceiro->id);
            $parceiro->delete();
            return redirect()->route('parceiros.index')->with('success', 'Parceiro deletado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'erro ao deletar Parceiro');
        }
    }
}
