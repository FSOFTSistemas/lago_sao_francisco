<?php

namespace App\Http\Controllers;

use App\Models\Movimento;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MovimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movimentos = Movimento::all();
        return view('movimento.index', compact('movimentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required',
            ]);
            Movimento::create($request->all());
            return redirect()->route('movimento.index')->with('success', 'Movimento criado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movimento $movimento)
    {
        try {
            $movimento = Movimento::findOrFail($movimento->id);
            $request->validate([
                'descricao' => 'required',
            ]);
            $movimento->update($request->all());
            return redirect()->route('movimento.index')->with('success', 'Movimento atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimento $movimento)
    {
        try {
            $movimento = Movimento::findOrFail($movimento->id);
            $movimento->delete();
            return redirect()->route('movimento.index')->with('success', 'Movimento deletado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar movimento');
        }
    }
}
