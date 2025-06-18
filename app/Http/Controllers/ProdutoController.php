<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
use App\Models\Cfop;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\Ncm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $categorias = CategoriaProduto::where('ativo', 1)->get();
        $empresas = Empresa::all();
        $produtos = Produto::all();
        $cfop = Cfop::all();
        $ncm = Ncm::all();
        return view('produto.create', compact('empresas', 'produtos', 'cfop', 'ncm', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        try {
            $request->validate([
                'descricao' => 'required|string',
                'categoria' => 'required|string',
                'ativo' => 'required|boolean',
                'ean' => 'nullable|string',
                'preco_custo' => 'nullable|numeric',
                'preco_venda' => 'required|numeric',
                'ncm' => 'nullable|string',
                'cst' => 'nullable|string',
                'cfop_interno' => 'nullable|string',
                'cfop_externo' => 'nullable|string',
                'aliquota' => 'nullable|numeric',
                'csosn' => 'nullable|string',
                'comissao' => 'nullable|numeric',
                'observacoes' => 'nullable|string'
            ],[
            'descricao.required' => 'A descrição do produto é obrigatória',
            'descricao.string' => 'A descrição deve ser um texto',
            
            'categoria.required' => 'A categoria do produto é obrigatória',
            'categoria.string' => 'A categoria deve ser um texto',
            
            'ativo.required' => 'O status (ativo/inativo) é obrigatório',
            'ativo.boolean' => 'O status deve ser verdadeiro ou falso',
            
            'ean.string' => 'O código EAN deve ser um texto',
            
            'preco_custo.numeric' => 'O preço de custo deve ser um número',
            
            'preco_venda.required' => 'O preço de venda é obrigatório',
            'preco_venda.numeric' => 'O preço de venda deve ser um número',
            
            'ncm.string' => 'O NCM deve ser um texto',
            
            'cst.string' => 'O CST deve ser um texto',
            
            'cfop_interno.string' => 'O CFOP interno deve ser um texto',
            
            'cfop_externo.string' => 'O CFOP externo deve ser um texto',
            
            'aliquota.numeric' => 'A alíquota deve ser um número',
            
            'csosn.string' => 'O CSOSN deve ser um texto',
            
            'empresa_id.exists' => 'A empresa selecionada é inválida',
            
            'comissao.numeric' => 'A comissão deve ser um número',
            
            'observacoes.string' => 'As observações devem ser um texto'
        ]);

            
            $request['empresa_id'] = Auth::user()->empresa_id;

            Produto::create($request->all());
            return redirect()->route('produto.index')->with('success', 'Produto criado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao validar dados: ' . $e->getMessage());
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
                'ativo' => 'required|boolean',
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
                'comissao' => 'nullable|numeric',
                'observacoes' => 'nullable|string'
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
