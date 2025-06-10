<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadeItemCategoria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DisponibilidadeItemCategoriaController extends Controller
{
    public function index()
    {
        $disponibilidades = DisponibilidadeItemCategoria::all();
        return view('cardapios.disponibilidade.index', compact('disponibilidades'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ItemInclusoPadrao' => 'required|boolean',
                'OrdemExibicao' => 'required|integer',
                'CategoriaItemID' => 'required|exists:categoria_itens,id',
                'ItemID' => 'required|exists:itens,id',
            ], [
                'ItemInclusoPadrao.required' => 'O campo Item Incluso Padrão é obrigatório.',
                'ItemInclusoPadrao.boolean' => 'O campo Item Incluso Padrão deve ser verdadeiro ou falso.',
                'OrdemExibicao.required' => 'O campo Ordem de Exibição é obrigatório.',
                'OrdemExibicao.integer' => 'O campo Ordem de Exibição deve ser um número inteiro.',
                'CategoriaItemID.required' => 'A categoria do item é obrigatória.',
                'CategoriaItemID.exists' => 'A categoria do item selecionada é inválida.',
                'ItemID.required' => 'O item é obrigatório.',
                'ItemID.exists' => 'O item selecionado é inválido.',
            ]);

            DisponibilidadeItemCategoria::create($validated);

            return redirect()->route('cardapios.disponibilidade.index')->with('success', 'Disponibilidade criada com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()->with($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao salvar a disponibilidade.');
        }
    }

    public function update(Request $request, DisponibilidadeItemCategoria $disponibilidadeItemCategoria)
    {
        try {
            $validated = $request->validate([
                'ItemInclusoPadrao' => 'required|boolean',
                'OrdemExibicao' => 'required|integer',
                'CategoriaItemID' => 'required|exists:categoria_itens,id',
                'ItemID' => 'required|exists:itens,id',
            ], [
                'ItemInclusoPadrao.required' => 'O campo Item Incluso Padrão é obrigatório.',
                'ItemInclusoPadrao.boolean' => 'O campo Item Incluso Padrão deve ser verdadeiro ou falso.',
                'OrdemExibicao.required' => 'O campo Ordem de Exibição é obrigatório.',
                'OrdemExibicao.integer' => 'O campo Ordem de Exibição deve ser um número inteiro.',
                'CategoriaItemID.required' => 'A categoria do item é obrigatória.',
                'CategoriaItemID.exists' => 'A categoria do item selecionada é inválida.',
                'ItemID.required' => 'O item é obrigatório.',
                'ItemID.exists' => 'O item selecionado é inválido.',
            ]);

            $disponibilidadeItemCategoria->update($validated);

            return redirect()->route('cardapios.disponibilidade.index')->with('success', 'Disponibilidade atualizada com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()->with($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar a disponibilidade.');
        }
    }

    public function destroy(DisponibilidadeItemCategoria $disponibilidadeItemCategoria)
    {
        try {
            $disponibilidadeItemCategoria->delete();
            return redirect()->route('cardapios.disponibilidade.index')->with('success', 'Disponibilidade excluída com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('cardapios.disponibilidade.index')->with('error', 'Erro ao excluir a disponibilidade.');
        }
    }
}
