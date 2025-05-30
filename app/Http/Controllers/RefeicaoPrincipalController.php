<?php

namespace App\Http\Controllers;

use App\Models\RefeicaoPrincipal;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefeicaoPrincipalController extends Controller
{
    public function index()
    {
        $refeicoes = RefeicaoPrincipal::all();
        return view('cardapios.refeicoes.index', compact('refeicoes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'NomeOpcaoRefeicao' => 'required|string|max:255',
                'PrecoPorPessoa' => 'required|numeric',
                'DescricaoOpcaoRefeicao' => 'nullable|string',
                'CardapioID' => 'required|exists:cardapios,id',
            ], [
                'NomeOpcaoRefeicao.required' => 'O nome da opção é obrigatório.',
                'NomeOpcaoRefeicao.string' => 'O nome da opção deve ser um texto.',
                'NomeOpcaoRefeicao.max' => 'O nome da opção não pode exceder 255 caracteres.',
                'PrecoPorPessoa.required' => 'O preço por pessoa é obrigatório.',
                'PrecoPorPessoa.numeric' => 'O preço por pessoa deve ser numérico.',
                'DescricaoOpcaoRefeicao.string' => 'A descrição deve ser um texto.',
                'CardapioID.required' => 'O cardápio é obrigatório.',
                'CardapioID.exists' => 'O cardápio selecionado é inválido.',
            ]);

            RefeicaoPrincipal::create($validated);

            return redirect()->route('refeicoes.index')->with('success', 'Refeição criada com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()->with($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao salvar a refeição.');
        }
    }


    public function update(Request $request, RefeicaoPrincipal $refeicaoPrincipal)
    {
        try {
            $validated = $request->validate([
                'NomeOpcaoRefeicao' => 'required|string|max:255',
                'PrecoPorPessoa' => 'required|numeric',
                'DescricaoOpcaoRefeicao' => 'nullable|string',
                'CardapioID' => 'required|exists:cardapios,id',
            ], [
                'NomeOpcaoRefeicao.required' => 'O nome da opção é obrigatório.',
                'NomeOpcaoRefeicao.string' => 'O nome da opção deve ser um texto.',
                'NomeOpcaoRefeicao.max' => 'O nome da opção não pode exceder 255 caracteres.',
                'PrecoPorPessoa.required' => 'O preço por pessoa é obrigatório.',
                'PrecoPorPessoa.numeric' => 'O preço por pessoa deve ser numérico.',
                'DescricaoOpcaoRefeicao.string' => 'A descrição deve ser um texto.',
                'CardapioID.required' => 'O cardápio é obrigatório.',
                'CardapioID.exists' => 'O cardápio selecionado é inválido.',
            ]);

            $refeicaoPrincipal->update($validated);

            return redirect()->route('cardapios.refeicoes.index')->with('success', 'Refeição atualizada com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()->with($e->getMessage())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar a refeição.');
        }
    }

    public function destroy(RefeicaoPrincipal $refeicaoPrincipal)
    {
        try {
            $refeicaoPrincipal->delete();
            return redirect()->route('cardapios.refeicoes.index')->with('success', 'Refeição excluída com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('refeicoes.index')->with('error', 'Erro ao excluir a refeição.');
        }
    }
}
