<?php

namespace App\Http\Controllers;

use App\Models\ContaCorrenteLancamento;
use Illuminate\Http\Request;

class ContaCorrenteLancamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contaCorrenteLancamentos = ContaCorrenteLancamento::all();
        return view('conta_corrente_lancamentos.index', compact('contaCorrenteLancamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('conta_corrente_lancamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saída',
                'status' => 'required|in:pendente,concluído',
                'banco_id' => 'required|exists:bancos,id',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            ContaCorrenteLancamento::create($request->all());
            return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('conta_corrente_lancamentos.index')->with('error', 'Erro ao cadastrar lançamento!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        return view('conta_corrente_lancamentos.show', compact('contaCorrenteLancamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        return view('conta_corrente_lancamentos.edit', compact('contaCorrenteLancamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        try {
            $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saída',
                'status' => 'required|in:pendente,concluído',
                'banco_id' => 'required|exists:bancos,id',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $contaCorrenteLancamento->update($request->all());
            return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('conta_corrente_lancamentos.index')->with('error', 'Erro ao atualizar lançamento!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContaCorrenteLancamento $contaCorrenteLancamento)
    {
        $contaCorrenteLancamento = ContaCorrenteLancamento::findOrFail($contaCorrenteLancamento->id);
        $contaCorrenteLancamento->delete();
        return redirect()->route('conta_corrente_lancamentos.index')->with('success', 'Lançamento excluído com sucesso!');
    }
}
