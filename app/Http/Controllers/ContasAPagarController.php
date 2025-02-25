<?php

namespace App\Http\Controllers;

use App\Models\ContasAPagar;
use Illuminate\Http\Request;

class ContasAPagarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contasAPagar = ContasAPagar::all();
        return view('contas_a_pagar.index', compact('contasAPagar'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contas_a_pagar.create');
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
                'valor_pago' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado',
                'empresa_id' => 'required|exists:empresas,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
                'fornecedor_id' => 'nullable|exists:fornecedores,id',
            ]);
            ContasAPagar::create($request->all());
            return redirect()->route('contas_a_pagar.index')->with('success', 'Conta a pagar cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contas_a_pagar.index')->with('error', 'Erro ao cadastrar conta a pagar!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContasAPagar $contasAPagar)
    {
        $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
        return view('contas_a_pagar.show', compact('contasAPagar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContasAPagar $contasAPagar)
    {
        $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
        return view('contas_a_pagar.edit', compact('contasAPagar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContasAPagar $contasAPagar)
    {
        try {
            $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'valor_pago' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado',
                'empresa_id' => 'required|exists:empresas,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
                'fornecedor_id' => 'nullable|exists:fornecedores,id',
            ]);
            $contasAPagar->update($request->all());
            return redirect()->route('contas_a_pagar.index')->with('success', 'Conta a pagar atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contas_a_pagar.index')->with('error', 'Erro ao atualizar conta a pagar!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAPagar $contasAPagar)
    {
        $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
        $contasAPagar->delete();
        return redirect()->route('contas_a_pagar.index')->with('success', 'Conta a pagar excluída com sucesso!');
    }
}
