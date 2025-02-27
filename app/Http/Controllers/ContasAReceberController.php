<?php

namespace App\Http\Controllers;

use App\Models\ContasAReceber;
use Illuminate\Http\Request;

class ContasAReceberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contasAReceber = ContasAReceber::all();
        return view('contas_a_receber.index', compact('contasAReceber'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contas_a_receber.create');
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
                'valor_recebido' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'data_recebimento' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado',
                'parcela' => 'required|integer',
                'venda_id' => 'nullable|exists:vendas,id',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id'
            ]);
            ContasAReceber::create($request->all());
            return redirect()->route('contas_a_receber.index')->with('success', 'Conta a receber cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contas_a_receber.index')->with('error', 'Erro ao cadastrar conta a receber!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContasAReceber $contasAReceber)
    {
        $contasAReceber = ContasAReceber::findOrFail($contasAReceber->id);
        return view('contas_a_receber.show', compact('contasAReceber'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContasAReceber $contasAReceber)
    {
        $contasAReceber = ContasAReceber::findOrFail($contasAReceber->id);
        return view('contas_a_receber.edit', compact('contasAReceber'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContasAReceber $contasAReceber)
    {
        try {
            $contasAReceber = ContasAReceber::findOrFail($contasAReceber->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'valor_recebido' => 'required|numeric',
                'data_vencimento' => 'required|date',
                'data_recebimento' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado',
                'parcela' => 'required|integer',
                'venda_id' => 'nullable|exists:vendas,id',
                'cliente_id' => 'required|exists:clientes,id',
                'plano_de_contas_id' => 'nullable|exists:plano_de_contas,id'
            ]);
            $contasAReceber->update($request->all());
            return redirect()->route('contas_a_receber.index')->with('success', 'Conta a receber atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('contas_a_receber.index')->with('error', 'Erro ao atualizar conta a receber!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContasAReceber $contasAReceber)
    {
        $contasAReceber = ContasAReceber::findOrFail($contasAReceber->id);
        $contasAReceber->delete();
        return redirect()->route('contas_a_receber.index')->with('success', 'Conta a receber excluída com sucesso!');
    }
}
