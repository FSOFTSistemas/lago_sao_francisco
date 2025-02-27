<?php

namespace App\Http\Controllers;

use App\Models\ContasAPagar;
use App\Models\Empresa;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContasAPagarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $planoDeContas = PlanoDeConta::all();
        $empresas = Empresa::all();
        $contasAPagar = ContasAPagar::all();
        return view('contasAPagar.index', compact('contasAPagar', 'planoDeContas', 'empresas'));
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
                'valor_pago' => 'nullable|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado',
                'empresa_id' => 'required|exists:empresas,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
                'fornecedor_id' => 'nullable|exists:fornecedores,id',
            ]);
            $request['empresa_id'] = PlanoDeConta::daEmpresa(Auth::user()->empresa_id);
            ContasAPagar::create($request->all());
            return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contasAPagar.index')->with('error', 'Erro ao cadastrar conta a pagar!');
        }
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
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
                'fornecedor_id' => 'nullable|exists:fornecedores,id',
            ]);

            $contasAPagar->update($request->all());
            return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contasAPagar.index')->with('error', 'Erro ao atualizar conta a pagar!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAPagar $contasAPagar)
    {
        $contasAPagar = ContasAPagar::findOrFail($contasAPagar->id);
        $contasAPagar->delete();
        return redirect()->route('contasAPagar.index')->with('success', 'Conta a pagar excluída com sucesso!');
    }
}
