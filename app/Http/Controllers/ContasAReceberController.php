<?php

namespace App\Http\Controllers;

use App\Models\ContasAReceber;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\PlanoDeConta;
use App\Models\Venda;
use Illuminate\Http\Request;

class ContasAReceberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contasAReceber = ContasAReceber::all();
        $clientes = Cliente::all();
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::all();
        $vendas = Venda::all();
        return view('contasAReceber.index', compact('contasAReceber', 'clientes', 'empresas', 'planoDeContas', 'vendas'));
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
                'valor_recebido' => 'nullable|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|string',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer',
                'cliente_id' => 'required|exists:clientes,id',
                'empresa_id' => 'required|exists:empresas,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
            ]);

            ContasAReceber::create($request->all());
            return redirect()->route('contasAReceber.index')->with('success', 'Conta a receber criada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContasAReceber $contasAReceber)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'valor_recebido' => 'nullable|numeric',
                'data_vencimento' => 'required|date',
                'data_pagamento' => 'nullable|date',
                'status' => 'required|string',
                'venda_id' => 'nullable|exists:vendas,id',
                'parcela' => 'nullable|integer',
                'cliente_id' => 'required|exists:clientes,id',
                'empresa_id' => 'required|exists:empresas,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id',
            ]);

            $contasAReceber->update($request->all());
            return redirect()->route('contas_a_receber.index')->with('success', 'Conta a receber atualizada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAReceber $contasAReceber)
    {
        try {
            $contasAReceber->delete();
            return redirect()->route('contas_a_receber.index')->with('success', 'Conta a receber deletada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar conta a receber');
        }
    }
}
