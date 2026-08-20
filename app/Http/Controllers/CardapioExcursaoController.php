<?php

namespace App\Http\Controllers;

use App\Models\CardapioExcursao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CardapioExcursaoController extends Controller
{
    public function index(): View
    {
        $cardapios = CardapioExcursao::query()
            ->withCount('almocosExcursao')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        return view('preferencias.cardapiosExcursao', compact('cardapios'));
    }

    public function store(Request $request): RedirectResponse
    {
        CardapioExcursao::create($this->validated($request));

        return redirect()
            ->route('cardapios-excursao.index')
            ->with('success', 'Cardápio de excursão cadastrado com sucesso!');
    }

    public function update(Request $request, CardapioExcursao $cardapiosExcursao): RedirectResponse
    {
        $cardapiosExcursao->update($this->validated($request, $cardapiosExcursao));

        return redirect()
            ->route('cardapios-excursao.index')
            ->with('success', 'Cardápio de excursão atualizado com sucesso!');
    }

    public function destroy(CardapioExcursao $cardapiosExcursao): RedirectResponse
    {
        if ($cardapiosExcursao->almocosExcursao()->exists()) {
            return redirect()
                ->route('cardapios-excursao.index')
                ->with('error', 'Este cardápio já foi usado em uma excursão. Inative-o para preservar o histórico.');
        }

        $cardapiosExcursao->delete();

        return redirect()
            ->route('cardapios-excursao.index')
            ->with('success', 'Cardápio de excursão excluído com sucesso!');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?CardapioExcursao $cardapio = null): array
    {
        $request->merge([
            'ativo' => $request->boolean('ativo'),
        ]);

        return $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cardapios_excursao', 'nome')->ignore($cardapio),
            ],
            'descricao_cardapio' => ['required', 'string', 'max:5000'],
            'valor_por_pessoa' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'ativo' => ['required', 'boolean'],
        ], [
            'nome.required' => 'Informe o nome do cardápio.',
            'nome.unique' => 'Já existe um cardápio de excursão com este nome.',
            'descricao_cardapio.required' => 'Descreva os itens do cardápio.',
            'descricao_cardapio.max' => 'A descrição do cardápio deve ter no máximo 5.000 caracteres.',
            'valor_por_pessoa.required' => 'Informe o valor por pessoa.',
            'valor_por_pessoa.numeric' => 'Informe um valor por pessoa válido.',
            'valor_por_pessoa.min' => 'O valor por pessoa deve ser maior que zero.',
        ]);
    }
}
