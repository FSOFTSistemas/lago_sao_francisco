<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Cliente;
use App\Models\Espaco;
use App\Models\FormaPagamento;
use App\Models\Adicional;
use App\Models\BuffetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AluguelController extends Controller
{
    public function index()
    {
        $aluguel = Aluguel::with(['cliente', 'espaco'])->latest()->paginate(15);
        return view('aluguel.index', compact('aluguel'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $espacos = Espaco::all();
        $formasPagamento = FormaPagamento::all();
        $itens = Adicional::all();
        $buffetItens = BuffetItem::all();

        return view('aluguel.create', compact(
            'clientes', 'espacos', 'formasPagamento', 'itens', 'buffetItens'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'cliente_id' => 'required|exists:clientes,id',
            'espaco_id' => 'required|exists:espacos,id',
            'forma_pagamento_id' => 'nullable|exists:forma_pagamentos,id',
            'observacoes' => 'nullable|string',
            'subtotal' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'acrescimo' => 'nullable|numeric',
            'desconto' => 'nullable|numeric',
            'parcelas' => 'nullable|integer',
            'vencimento' => 'nullable|date',
            'contrato' => 'nullable|string',
            'status' => 'nullable|string',
            'numero_pessoas_buffet' => 'nullable|integer|min:1',
        ]);

        $validated['empresa_id'] = Auth::user()->empresa_id;

        $aluguel = Aluguel::create($validated);

        // Relacionar itens adicionais
        $aluguel->adicionais()->sync($request->input('itens', []));

        // Relacionar buffet itens
        $aluguel->buffetItens()->sync($request->input('buffet_itens', []));

        return redirect()->route('aluguel.index')->with('success', 'Aluguel criado com sucesso!');
    }

    public function edit($id)
    {
        $aluguel = Aluguel::find($id);
        $clientes = Cliente::all();
        $espacos = Espaco::all();
        $formasPagamento = FormaPagamento::all();
        $itens = Adicional::all();
        $buffetItens = BuffetItem::all();

        return view('aluguels.create', compact(
            'aluguel', 'clientes', 'espacos', 'formasPagamento', 'itens', 'buffetItens'
        ));
    }

    public function update(Request $request, Aluguel $aluguel)
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'cliente_id' => 'required|exists:clientes,id',
            'espaco_id' => 'required|exists:espacos,id',
            'forma_pagamento_id' => 'nullable|exists:forma_pagamentos,id',
            'observacoes' => 'nullable|string',
            'subtotal' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'acrescimo' => 'nullable|numeric',
            'desconto' => 'nullable|numeric',
            'parcelas' => 'nullable|integer',
            'vencimento' => 'nullable|date',
            'contrato' => 'nullable|string',
            'status' => 'nullable|string',
            'numero_pessoas_buffet' => 'nullable|integer|min:1',
        ]);

        $aluguel->update($validated);

        // Relacionar itens adicionais
        $aluguel->adicionais()->sync($request->input('itens', []));

        // Relacionar buffet itens
        $aluguel->buffetItens()->sync($request->input('buffet_itens', []));

        return redirect()->route('aluguels.index')->with('success', 'Aluguel atualizado com sucesso!');
    }

    public function destroy(Aluguel $aluguel)
    {
        $aluguel->delete();
        return redirect()->route('aluguel.index')->with('success', 'Aluguel excluído com sucesso!');
    }
}
