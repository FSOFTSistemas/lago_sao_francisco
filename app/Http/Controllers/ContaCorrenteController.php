<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\ContaCorrente;
use Illuminate\Http\Request;

class ContaCorrenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banco = Banco::all();
        $contaCorrente = ContaCorrente::all();
        return view('contaCorrente.index', compact('contaCorrente', 'banco'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'numero_conta' => 'required|string',
                'titular' => 'nullable|string',
                'saldo' => 'nullable|numeric',
                'banco_id' => 'required|exists:bancos,id',
            ]);
            ContaCorrente::create($request->all());
            return redirect()->route('contaCorrente.index')->with('success', 'Conta corrente cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contaCorrente.index')->with('error', 'Erro ao cadastrar conta corrente!');

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContaCorrente $contaCorrente)
    {
        try {
            $contaCorrente = ContaCorrente::findOrFail($contaCorrente->id);
            $request->validate([
                'descricao' => 'required|string',
                'numero_conta' => 'required|string',
                'titular' => 'nullable|string',
                'saldo' => 'nullable|numeric',
                'banco_id' => 'required|exists:bancos,id',
            ]);
            $contaCorrente->update($request->all());
            return redirect()->route('contaCorrente.index')->with('success', 'Conta corrente atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contaCorrente.index')->with('error', 'Erro ao atualizar conta corrente!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContaCorrente $contaCorrente)
    {
        $contaCorrente = ContaCorrente::findOrFail($contaCorrente->id);
        $contaCorrente->delete();
        return redirect()->route('contaCorrente.index')->with('success', 'Conta corrente deletada com sucesso!');
    }
}
