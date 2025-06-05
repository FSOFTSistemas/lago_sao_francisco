<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\CategoriasDeItensCardapio;
use App\Models\DisponibilidadeItemCategoria;
use App\Models\RefeicaoPrincipal;
use App\Models\SecoesCardapio;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use View;

class CategoriasDeItensCardapioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = CategoriasDeItensCardapio::all();
        $secoes = SecoesCardapio::all();
        $refeicoes = RefeicaoPrincipal::all();
        return view('categoriaItensCardapio.index', compact('categorias', 'secoes', 'refeicoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categoriaItensCardapio._form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      try {
            DB::beginTransaction();
             $request->merge([
                'eh_grupo_escolha_exclusiva' => $request->has('eh_grupo_escolha_exclusiva')
            ]);
            $validated = $request->validate([
                'sessao_cardapio_id' => 'nullable',
                'refeicao_principal_id' => 'nullable|exists:refeicao_principals,id',
                'nome_categoria_item' => 'required|string|max:255',
                'numero_escolhas_permitidas' => 'required|integer',
                'eh_grupo_escolha_exclusiva' => 'required|boolean',
                'ordem_exibicao' => 'required|integer',
                'itens' => 'sometimes|array', // 'sometimes' permite que seja opcional
                
            ], [
                'refeicao_principal_id.exists' => 'A refeição principal selecionada é inválida.',
                'nome_categoria_item.required' => 'O campo nome da categoria é obrigatório.',
                'nome_categoria_item.string' => 'O nome da categoria deve ser um texto.',
                'nome_categoria_item.max' => 'O nome da categoria não pode ter mais que 255 caracteres.',
                'numero_escolhas_permitidas.required' => 'O campo número de escolhas permitidas é obrigatório.',
                'numero_escolhas_permitidas.integer' => 'O número de escolhas permitidas deve ser um valor inteiro.',
                'eh_grupo_escolha_exclusiva.required' => 'O campo grupo de escolha exclusiva é obrigatório.',
                'eh_grupo_escolha_exclusiva.boolean' => 'O campo grupo de escolha exclusiva deve ser verdadeiro ou falso.',
                'ordem_exibicao.required' => 'O campo ordem de exibição é obrigatório.',
                'ordem_exibicao.integer' => 'A ordem de exibição deve ser um valor inteiro.',
                'itens.array' => 'Os itens devem ser enviados como uma lista.',    
            ]);

            // Cria a categoria
            $categoria = CategoriasDeItensCardapio::create($validated);
            
            // Processa os itens
            foreach($validated['itens'] as $index => $item) {
                $dadosItem = [
                    'ItemInclusoPadrao' => true,
                    'OrdemExibicao' => $index + 1,
                    'CategoriaItemID' => $categoria->id,
                    'ItemID' => $item['id']
                ];

                // Verificação adicional
                if (!is_numeric($item['id'])) {
                    throw new Exception("ID do item inválido: " . $item['id']);
                }
                
                DisponibilidadeItemCategoria::create($dadosItem);
            }

            DB::commit();
            
            return redirect()->route('categoriaItensCardapio.index')->with('success', 'Categoria criada com sucesso');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()
                ->route('categoriaItensCardapio.index');

                
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Registro não encontrado')
                ->withInput();
                
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Erro ao criar categoria: " . $e->getMessage());
            return redirect()
                ->route('categoriaItensCardapio.index')
                ->with('error', "Erro ao processar: " . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CategoriasDeItensCardapio $categoriasDeItensCardapio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('categoriaItensCardapio._form', ['id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = CategoriasDeItensCardapio::findOrFail($id);

            $validated = $request->validate([
                'sessao_cardapio_id' => 'sometimes|required|exists:secoes_cardapios,id',
                'refeicao_principal_id' => 'sometimes|required|exists:refeicao_principals,id',
                'nome_categoria_item' => 'sometimes|required|string|max:255',
                'numero_escolhas_permitidas' => 'sometimes|required|integer',
                'eh_grupo_escolha_exclusiva' => 'sometimes|required|boolean',
                'ordem_exibicao' => 'sometimes|required|integer',
            ]);

            $categoria->update($validated);

            return redirect()->route('categoriaItensCardapio.index')->with('success', 'Categoria atualizada com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Categoria não encontrada');
        } catch (Exception $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Erro ao atualizar categoria');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       try {
            $categoria = CategoriasDeItensCardapio::findOrFail($id);
            $categoria->delete();

            return redirect()->route('categoriaItensCardapio.index')->with('success', 'Categoria deletada com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Categoria não foi encontrada');
        } catch (Exception $e) {
            return redirect()->route('categoriaItensCardapio.index')->with('error', 'Erro ao deletar categoria');
        }
    }
}
