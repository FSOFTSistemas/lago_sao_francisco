<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Empresa;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = Empresa::all();
        $produtos = Produto::all();
        return view('produto.index', compact('empresas', 'produtos'));
    }

    public function create()
    {
        $empresas = Empresa::all();
        $produtos = Produto::all();
        return view('produto.create', compact('empresas', 'produtos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|string',
                'situacao' => 'required|string',
                'ean' => 'nullable|string',
                'preco_custo' => 'nullable|numeric',
                'preco_venda' => 'required|numeric',
                'ncm' => 'nullable|string',
                'cst' => 'nullable|string',
                'cfop_interno' => 'nullable|string',
                'cfop_externo' => 'nullable|string',
                'aliquota' => 'nullable|numeric',
                'csosn' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            Produto::create($request->all());
            return redirect()->route('produto.index')->with('success', 'Produto criado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|string',
                'situacao' => 'required|string',
                'ean' => 'nullable|string',
                'preco_custo' => 'nullable|numeric',
                'preco_venda' => 'required|numeric',
                'ncm' => 'nullable|string',
                'cst' => 'nullable|string',
                'cfop_interno' => 'nullable|string',
                'cfop_externo' => 'nullable|string',
                'aliquota' => 'nullable|numeric',
                'csosn' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            $produto->update($request->all());
            return redirect()->route('produto.index')->with('success', 'Produto atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    public function edit(Produto $produto)
    {
        $produto = Produto::findOrFail($produto->id);
        return view('produto.create', compact('produto'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        try {
            $produto->delete();
            return redirect()->route('produto.index')->with('success', 'Produto deletado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar produto');
        }
    }
}
