<?php

namespace App\Http\Controllers;

use App\Models\Cardapio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CardapioController extends Controller
{
    public function index()
    {
        try {
            $cardapios = Cardapio::all();
            return view('cardapios.index', compact('cardapios'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('cardapios._form');
    }

    public function edit($id) 
    {
        return view('cardapios._form', ['id' => $id]);
    }

    public function show(Cardapio $cardapio)
    {
        return view('cardapios.show', compact('cardapio'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'NomeCardapio' => 'required|string|max:255',
                'AnoCardapio' => 'nullable|integer',
                'ValidadeOrcamentoDias' => 'nullable|integer',
                'PoliticaCriancaGratisLimiteIdade' => 'nullable|integer',
                'PoliticaCriancaDescontoPercentual' => 'nullable|numeric',
                'PoliticaCriancaDescontoIdadeInicio' => 'nullable|integer',
                'PoliticaCriancaDescontoIdadeFim' => 'nullable|integer',
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 'nullable|integer',
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => 'nullable|boolean',
            ], [
                'NomeCardapio.required' => 'O campo Nome do Cardápio é obrigatório.',
                'NomeCardapio.string' => 'O campo Nome do Cardápio deve ser uma string.',
                'NomeCardapio.max' => 'O campo Nome do Cardápio não pode exceder 255 caracteres.',
                'AnoCardapio.integer' => 'O campo Ano do Cardápio deve ser um número inteiro.',
                'ValidadeOrcamentoDias.integer' => 'O campo Validade do Orçamento deve ser um número inteiro.',
                'PoliticaCriancaGratisLimiteIdade.integer' => 'O campo Limite de Idade para Criança Grátis deve ser um número inteiro.',
                'PoliticaCriancaDescontoPercentual.numeric' => 'O campo Percentual de Desconto para Criança deve ser um número.',
                'PoliticaCriancaDescontoIdadeInicio.integer' => 'O campo Idade Inicial para Desconto de Criança deve ser um número inteiro.',
                'PoliticaCriancaDescontoIdadeFim.integer' => 'O campo Idade Final para Desconto de Criança deve ser um número inteiro.',
                'PoliticaCriancaPrecoIntegralIdadeInicio.integer' => 'O campo Idade Inicial para Preço Integral deve ser um número inteiro.',
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao.boolean' => 'O campo Opção de Escolha do Conteúdo Principal deve ser verdadeiro ou falso.',
            ]);

            $cardapio = Cardapio::create($validated);
            return redirect()->route('cardapios.show', $cardapio->CardapioID);
            // return redirect()->route('cardapios.index')->with('success', 'Cardapio criado com sucesso!');
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Cardapio $cardapio)
    {
        try {
            $validated = $request->validate([
                'NomeCardapio' => 'required|string|max:255',
                'AnoCardapio' => 'nullable|integer',
                'ValidadeOrcamentoDias' => 'nullable|integer',
                'PoliticaCriancaGratisLimiteIdade' => 'nullable|integer',
                'PoliticaCriancaDescontoPercentual' => 'nullable|numeric',
                'PoliticaCriancaDescontoIdadeInicio' => 'nullable|integer',
                'PoliticaCriancaDescontoIdadeFim' => 'nullable|integer',
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 'nullable|integer',
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => 'nullable|boolean',
            ], [
                'NomeCardapio.required' => 'O campo Nome do Cardápio é obrigatório.',
                'NomeCardapio.string' => 'O campo Nome do Cardápio deve ser uma string.',
                'NomeCardapio.max' => 'O campo Nome do Cardápio não pode exceder 255 caracteres.',
                'AnoCardapio.integer' => 'O campo Ano do Cardápio deve ser um número inteiro.',
                'ValidadeOrcamentoDias.integer' => 'O campo Validade do Orçamento deve ser um número inteiro.',
                'PoliticaCriancaGratisLimiteIdade.integer' => 'O campo Limite de Idade para Criança Grátis deve ser um número inteiro.',
                'PoliticaCriancaDescontoPercentual.numeric' => 'O campo Percentual de Desconto para Criança deve ser um número.',
                'PoliticaCriancaDescontoIdadeInicio.integer' => 'O campo Idade Inicial para Desconto de Criança deve ser um número inteiro.',
                'PoliticaCriancaDescontoIdadeFim.integer' => 'O campo Idade Final para Desconto de Criança deve ser um número inteiro.',
                'PoliticaCriancaPrecoIntegralIdadeInicio.integer' => 'O campo Idade Inicial para Preço Integral deve ser um número inteiro.',
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao.boolean' => 'O campo Opção de Escolha do Conteúdo Principal deve ser verdadeiro ou falso.',
            ]);

            $cardapio->update($validated);
            return redirect()->route('cardapios.index')->with('success', 'Cardapio atualizado com sucesso!');
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Cardapio $cardapio)
    {
        try {
            $cardapio->delete();
            return redirect()->route('cardapios.index')->with('success', 'Cardapio deletado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

        public function verPdf($id)
    {

        $cardapio = Cardapio::with([
            'secoes.categorias.itens.item',
            'opcoes.categorias.itens.item'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('cardapios.pdf', compact('cardapio'));
        return $pdf->stream("cardapio_{$cardapio->id}.pdf");
    }

     public function dados($cardapioId)
{
     try {
            $cardapio = Cardapio::with([
                'secoes.categorias.itens.item',
                'opcoes.categorias.itens.item'
            ])->findOrFail($cardapioId);
            
            return response()->json([
                'secoes' => $cardapio->secoes,
                'opcoes' => $cardapio->opcoes
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cardápio não encontrado'], 404);
        }
}

}
