<?php

namespace App\Http\Controllers;

use App\Models\VendaCartao;
use Illuminate\Http\Request;

class VendaCartaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendaCartao = VendaCartao::all();
        return view('vendaCartao.index', compact('vendaCartao'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendaCartao.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'conta_id' => 'required',
                'banco_id' => 'required|exists:bancos,id',
                'cliente_id' => 'nullable|exists:clientes,id',
                'venda_id' => 'required|exists:vendas,id',
                'valor' => 'required|numeric',
                'data_baixa' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado,cancelado',
                'taxa' => 'required|numeric',
                'parcela' => 'nullable|integer',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
        } catch (\Exception $e) {
            dd($e)->getMessage();

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VendaCartao $vendaCartao)
    {
        $vendaCartao = VendaCartao::find($vendaCartao);
        return view('vendaCartao.show', compact('vendaCartao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VendaCartao $vendaCartao)
    {
        $vendaCartao = VendaCartao::find($vendaCartao);
        return view('vendaCartao.edit', compact('vendaCartao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendaCartao $vendaCartao)
    {
        try{
            $vendaCartao = VendaCartao::findOrFail($vendaCartao->id);
            $request->validate([
                'conta_id' => 'required',
                'banco_id' => 'required|exists:bancos,id',
                'cliente_id' => 'nullable|exists:clientes,id',
                'venda_id' => 'required|exists:vendas,id',
                'valor' => 'required|numeric',
                'data_baixa' => 'nullable|date',
                'status' => 'required|in:pendente,finalizado,cancelado',
                'taxa' => 'required|numeric',
                'parcela' => 'nullable|integer',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $vendaCartao->update($request->all());
            return redirect()->route('vendaCartao.index')->with('success', 'Venda de cartão atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('vendaCartao.index')->with('error', 'Erro ao atualizar a venda de cartão!');
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendaCartao $vendaCartao)
    {
        $vendaCartao = VendaCartao::findOrFail($vendaCartao->id);
        $vendaCartao->delete();
        return redirect()->route('vendaCartao.index')->with('success', 'Venda de cartão excluída com sucesso!');
    }
}
