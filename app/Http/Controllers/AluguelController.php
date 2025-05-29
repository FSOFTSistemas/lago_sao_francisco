<?php

namespace App\Http\Controllers;

use App\Models\Aluguel;
use App\Models\Cliente;
use App\Models\Espaco;
use App\Models\FormaPagamento;
use App\Models\Adicional;
use App\Models\AluguelCategoriaItem;
use App\Models\BuffetItem;
use App\Models\Cardapio;
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
        $cardapios = Cardapio::all();

        return view('aluguel.create', compact(
            'clientes', 'espacos', 'formasPagamento', 'itens', 'buffetItens', 'cardapios'
        ));
    }

  public function store(Request $request)
{
    try {

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
            'cardapio_id' => 'nullable|exists:cardapios,id',
            'categorias' => 'nullable|array',
            'categorias.*.itens' => 'nullable|array',
            'categorias.*.itens.*' => 'exists:buffet_items,id',
        ]);
    
        $validated['empresa_id'] = Auth::user()->empresa_id;
    
        // Criação do aluguel
        $aluguel = Aluguel::create($validated);
    
        // Relacionar itens adicionais
        $aluguel->adicionais()->sync($request->input('itens', []));
    
        // Salvar os itens de buffet agrupados por categoria
        foreach ($request->input('categorias', []) as $categoriaId => $dados) {
            foreach ($dados['itens'] ?? [] as $itemId) {
                AluguelCategoriaItem::create([
                    'aluguel_id' => $aluguel->id,
                    'cardapio_categoria_id' => $categoriaId,
                    'buffet_item_id' => $itemId,
                ]);
            }
        }
    
        return redirect()->route('aluguel.index')->with('success', 'Aluguel criado com sucesso!');
    } catch( \Exception $e) {
        return redirect()->back()->with('error', 'Erro ao cadastrar Aluguel');
        dd($e->getMessage());
    }
}


   public function edit(Aluguel $aluguel)
{
    // Carregar relações necessárias para o formulário
    $aluguel->load([
        'cliente',
        'espaco',
        'adicionais',
        'cardapio.categorias.itens',
        'formaPagamento',
        'buffetItens',
        'aluguelCategoriaItems.buffetItem'
    ]);

    $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome_razao_social')->get();
    $espacos = Espaco::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
    $itens = Adicional::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
    $cardapios = Cardapio::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
    $formasPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();

    return view('aluguel.edit', compact(
        'aluguel',
        'clientes',
        'espacos',
        'itens',
        'cardapios',
        'formasPagamento'
    ));
}


    public function update(Request $request, Aluguel $aluguel)
{
    try {
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
        'cardapio_id' => 'nullable|exists:cardapios,id',
        'categorias' => 'nullable|array',
        'categorias.*.itens' => 'nullable|array',
        'categorias.*.itens.*' => 'exists:buffet_items,id',
    ]);

    $validated['empresa_id'] = Auth::user()->empresa_id;

    $aluguel->update($validated);

    // Atualizar itens adicionais
    $aluguel->adicionais()->sync($request->input('itens', []));

    // Limpar e recriar os itens do buffet por categoria
    AluguelCategoriaItem::where('aluguel_id', $aluguel->id)->delete();

    $categoriasSelecionadas = $request->input('categorias', []);

    foreach ($categoriasSelecionadas as $categoriaId => $dados) {
        foreach ($dados['itens'] ?? [] as $itemId) {
            AluguelCategoriaItem::create([
                'aluguel_id' => $aluguel->id,
                'cardapio_categoria_id' => $categoriaId,
                'buffet_item_id' => $itemId,
            ]);
        }
    }

    // Atualizar a relação many-to-many (caso esteja usando também)
    $aluguel->buffetItens()->sync($request->input('buffet_itens', []));

    return redirect()->route('aluguel.index')->with('success', 'Aluguel atualizado com sucesso!');
    } catch(\Exception $e) {
        return redirect()->back()->with('error', 'Erro ao Atualizar Aluguel');
    }
}


    public function destroy(Aluguel $aluguel)
    {
        try{
        $aluguel->delete();
        return redirect()->route('aluguel.index')->with('success', 'Aluguel excluído com sucesso!');
        } catch(\Exception $e) {
            return redirect()->back()->with('error', 'erro ao Deletar Aluguel');
        }
    }
}
