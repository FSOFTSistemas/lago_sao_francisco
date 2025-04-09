<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;

class AluguelController extends Controller
{
    public function index()
    {
        $empresa = Empresa::all();
        $cliente = Cliente::all();
        $aluguel = Aluguel::all();
        $formaPagamento = FormaPagamento::all();
        return view('aluguel.index', compact('empresa', 'cliente', 'aluguel', 'formaPagamento'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('aluguel.create');
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'data' => 'required|date',
                'observacoes' => 'string|nullable',
                'subtotal' => 'numeric',
                'total' => 'numeric',
                'acrescimo',
                'desconto',
                'parcelas' => 'numeric|nullable',
                'vencimento' => 'date|required',
                'contrato' => '',
                'adicionais',
                'status' => 'required|in:',
                'espaco_id' => 'required|exists:espacos,id',
                'cliente_id' => 'required|exists:clientes,id',
                'empresa_id' => 'required|exists:empresas,id',
                'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            ]);
            Aluguel::create($request->all());
            return redirect()->route('aluguel.index')->with('success', 'Aluguel criado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar aluguel');
        }
    }

    public function edit(Aluguel $aluguel)
    {
        $aluguel = Aluguel::findOrFail($aluguel->id);
        return view('aluguel.create', compact('aluguel'));
    }

    public function update(Request $request, Aluguel $aluguel)
    {
        try{
            $request->validate([
                'data' => 'required|date',
                'observacoes' => 'string|nullable',
                'subtotal' => 'numeric',
                'total' => 'numeric',
                'acrescimo',
                'desconto',
                'parcelas' => 'numeric|nullable',
                'vencimento' => 'date|required',
                'contrato' => '',
                'adicionais',
                'status' => 'required|in:',
                'espaco_id' => 'required|exists:espacos,id',
                'cliente_id' => 'required|exists:clientes,id',
                'empresa_id' => 'required|exists:empresas,id',
                'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            ]);
            $aluguel->update($request->all());
            return redirect()->route('aluguel.index')->with('success', 'Aluguel atualizado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar aluguel');
        }
    }

    public function destroy(Aluguel $aluguel)
    {
        try {
            $aluguel = Aluguel::findOrFail($aluguel->id());
            $aluguel->delete();
            return redirect()->route('aluguel.index')->with('success', 'Aluguel deletado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar aluguel');
        }
    }
}
