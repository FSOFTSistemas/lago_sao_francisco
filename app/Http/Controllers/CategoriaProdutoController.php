<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
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
use Illuminate\Support\Facades\Log;



class CategoriaProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = CategoriaProduto::with('produtos')->get();
        return view('categoriaProduto.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      try {
           $validated = $request->validate([
                'descricao' => 'required|string|max:50|min:3',
            ], [
                'descricao.required' => 'A descrição é obrigatória.',
                'descricao.string'   => 'A descrição deve ser um texto.',
                'descricao.max'      => 'A descrição não pode ter mais que 50 caracteres.',
                'descricao.min'      => 'A descrição deve ter pelo menos 3 caracteres.',
            ]);
            

            // Cria a categoria
            $categoria = CategoriaProduto::create($validated);
            
            return redirect()->backwithinput()->with('success', 'Categoria de produto criada com sucesso');
        } catch (ValidationException $e) {
            Log::error("Erro ao criar categoria: " . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', "Erro ao validar: " . $e->getMessage())
                ->withInput();

                
        } catch (ModelNotFoundException $e) {

            return redirect()
                ->back()
                ->with('error', 'Registro não encontrado')
                ->withInput();
                
        } catch (Exception $e) {

            Log::error("Erro ao criar categoria: " . $e->getMessage());
            return redirect()
                ->back()
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
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            
            $categoria = CategoriaProduto::findOrFail($id);
            $validated = $request->validate([
                'descricao' => 'required|string|max:50|min:3',
                'ativo' => 'nullable|boolean',
            ], [
                'descricao.required' => 'A descrição é obrigatória.',
                'descricao.string'   => 'A descrição deve ser um texto.',
                'descricao.max'      => 'A descrição não pode ter mais que 50 caracteres.',
                'descricao.min'      => 'A descrição deve ter pelo menos 3 caracteres.',
                'ativo.boolean'      => 'O campo ativo deve ser verdadeiro ou falso.',
            ]);
            $ativo = $request->has('ativo') ? 1 : 0;
            $validated['ativo'] = $ativo;
            //dd($validated);
            $categoria->update($validated);


            return redirect()->route('categoriaProduto.index')->with('success', 'Categoria atualizada com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaProduto.index')->with('error', 'Categoria não encontrada');
        } catch (Exception $e) {
            return redirect()->route('categoriaProduto.index')->with('error', 'Erro ao atualizar categoria');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       try {
            $categoria = CategoriaProduto::findOrFail($id);
            $categoria->delete();

            return redirect()->route('categoriaProduto.index')->with('success', 'Categoria deletada com sucesso');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('categoriaProduto.index')->with('error', 'Categoria não foi encontrada');
        } catch (Exception $e) {
            return redirect()->route('categoriaProduto.index')->with('error', 'Erro ao deletar categoria');
        }
    }
}
