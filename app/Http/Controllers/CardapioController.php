<?php

namespace App\Http\Controllers;

use App\Models\Cardapio;
use App\Models\CardapioCategoriaItem;
use App\Models\CategoriasCardapio;
use Illuminate\Http\Request;

class CardapioController extends Controller
{
    public function index()
    {
        $cardapios = Cardapio::with('categorias')->get();
        return view('cardapios.index', compact('cardapios'));
    }

    public function create()
    {
        $categorias = CategoriasCardapio::with('itens')->get();
        return view('cardapios.create', compact('categorias'));
    }

    public function store(Request $request)
    {
       $cardapio = Cardapio::create($request->only('nome', 'observacoes'));

    if ($request->has('categorias')) {
        foreach ($request->categorias as $categoriaId => $data) {
            if (!empty($data['quantidade']) && intval($data['quantidade']) > 0) {
                // Salva categoria no cardápio
                $cardapio->categorias()->attach($categoriaId, [
                    'quantidade_itens' => intval($data['quantidade']),
                ]);

                // Salva itens permitidos para essa categoria nesse cardápio
                if (!empty($data['itens']) && is_array($data['itens'])) {
                    foreach ($data['itens'] as $itemId) {
                        CardapioCategoriaItem::create([
                            'cardapio_id' => $cardapio->id,
                            'categoria_id' => $categoriaId,
                            'buffet_item_id' => $itemId,
                        ]);
                    }
                }
            }
        }
    }

        return redirect()->route('cardapios.index')->with('success', 'Cardápio criado com sucesso!');
    }

    public function edit($id)
{
    $cardapio = Cardapio::findOrFail($id);
    $categorias = CategoriasCardapio::with('itens')->get();

    $selecionadas = $cardapio->categorias->pluck('pivot.quantidade_itens', 'id')->toArray();

    $itensSelecionados = [];
    foreach ($categorias as $categoria) {
        $itens = \App\Models\CardapioCategoriaItem::where('cardapio_id', $cardapio->id)
            ->where('categoria_id', $categoria->id)
            ->pluck('buffet_item_id')
            ->toArray();
        $itensSelecionados[$categoria->id] = $itens;
    }

    return view('cardapios.create', compact('cardapio', 'categorias', 'selecionadas', 'itensSelecionados'));
}


    public function update(Request $request, Cardapio $cardapio)
{
    $cardapio->update($request->only('nome', 'observacoes'));

    // Remove os registros antigos
    $cardapio->categorias()->detach();
    \App\Models\CardapioCategoriaItem::where('cardapio_id', $cardapio->id)->delete();

    if ($request->has('categorias')) {
        foreach ($request->categorias as $categoriaId => $data) {
            if (!empty($data['quantidade']) && intval($data['quantidade']) > 0) {
                $cardapio->categorias()->attach($categoriaId, [
                    'quantidade_itens' => intval($data['quantidade']),
                ]);

                if (!empty($data['itens']) && is_array($data['itens'])) {
                    foreach ($data['itens'] as $itemId) {
                        \App\Models\CardapioCategoriaItem::create([
                            'cardapio_id' => $cardapio->id,
                            'categoria_id' => $categoriaId,
                            'buffet_item_id' => $itemId,
                        ]);
                    }
                }
            }
        }
    }

    return redirect()->route('cardapios.index')->with('success', 'Cardápio atualizado!');
}


    public function destroy(Cardapio $cardapio)
    {
        $cardapio->delete();
        return redirect()->route('cardapios.index')->with('success', 'Cardápio removido!');
    }


    public function dados($id)
{
    $cardapio = Cardapio::with('categorias')->findOrFail($id);

    $dados = $cardapio->categorias->map(function ($categoria) use ($id) {
        // Pega o limite de itens da pivot cardapio_categoria
        $quantidade_itens = $categoria->pivot->quantidade_itens ?? 0;

        // Busca os itens filtrando na tabela cardapio_categoria_item (pivot)
        $itens = \App\Models\BuffetItem::select('buffet_items.id', 'buffet_items.nome')
            ->join('cardapio_categoria_item', function ($join) use ($id, $categoria) {
                $join->on('buffet_items.id', '=', 'cardapio_categoria_item.buffet_item_id')
                     ->where('cardapio_categoria_item.cardapio_id', $id)
                     ->where('cardapio_categoria_item.categoria_id', $categoria->id);
            })
            ->get();

        return [
            'id' => $categoria->id,
            'nome' => $categoria->nome,
            'quantidade_itens' => $quantidade_itens,
            'itens' => $itens,
        ];
    });

    return response()->json($dados);
}



//     public function dados($id)
// {
    // $cardapio = Cardapio::with(['categorias', 'categorias.itens'])->findOrFail($id);

//     $dados = $cardapio->categorias->map(function ($categoria) {
//         return [
//             'id' => $categoria->id,
//             'nome' => $categoria->nome,
//             'quantidade_itens' => $categoria->pivot->quantidade_itens,
//             'itens' => $categoria->itens->map(function ($item) {
//                 return [
//                     'id' => $item->id,
//                     'nome' => $item->nome,
//                 ];
//             }),
//         ];
//     });

//     return response()->json($dados);
// }

}

