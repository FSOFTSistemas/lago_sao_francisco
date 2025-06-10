<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FormaPagamento;
use App\Models\User;
use Illuminate\Http\Request;

class VendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = Empresa::all();
        $usuarios = User::all();
        $clientes = Cliente::all();
        $formaPagamento = FormaPagamento::all();
        return view('venda.index', compact('empresas', 'usuarios', 'clientes', 'formaPagamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'data' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'usuario_id' => 'required|exists:users,id',
            'total' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'desconto' => 'nullable|numeric',
            'acrescimo' => 'nullable|numeric',
            'situacao' => 'required|string',
            'gerado_nf' => 'required|boolean',
            ]);
            Venda::create($request->all());
            return redirect()->route('venda.index')->with('success', 'Venda criada com sucesso');
        } catch (\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venda $venda)
    {
        try {
            $request->validate([
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            'empresa_id' => 'required|exists:empresas,id',
            'data' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'usuario_id' => 'required|exists:users,id',
            'total' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'desconto' => 'nullable|numeric',
            'acrescimo' => 'nullable|numeric',
            'situacao' => 'required|string',
            'gerado_nf' => 'required|boolean',
            ]);
            $venda->update($request->all());
            return redirect()->route('venda.index')->with('success', 'Venda atualizada com sucesso');
        } catch (\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venda $venda)
    {
        try {
            $venda = Venda::findOrFail($venda->id);
            $venda->delete();
            return redirect()->route('venda.index')->with('success', 'Venda deletada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar venda');
        }
    }
}
