<?php

namespace App\Http\Controllers;

use App\Models\ContaCorrente;
use Illuminate\Http\Request;

class ContaCorrenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contasCorrentes = ContaCorrente::all();
        return view('conta_corrente.index', compact('contasCorrentes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('conta_corrente.create');
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
            return redirect()->route('conta_corrente.index')->with('success', 'Conta corrente cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('conta_corrente.index')->with('error', 'Erro ao cadastrar conta corrente!');

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContaCorrente $contaCorrente)
    {
        $contaCorrente = ContaCorrente::findOrFail($contaCorrente->id);
        return view('conta_corrente.show', compact('contaCorrente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContaCorrente $contaCorrente)
    {
        $contaCorrente = ContaCorrente::findOrFail($contaCorrente->id);
        return view('conta_corrente.edit', compact('contaCorrente'));
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
            return redirect()->route('conta_corrente.index')->with('success', 'Conta corrente atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('conta_corrente.index')->with('error', 'Erro ao atualizar conta corrente!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContaCorrente $contaCorrente)
    {
        $contaCorrente = ContaCorrente::findOrFail($contaCorrente->id);
        $contaCorrente->delete();
        return redirect()->route('conta_corrente.index')->with('success', 'Conta corrente deletada com sucesso!');
    }
}
