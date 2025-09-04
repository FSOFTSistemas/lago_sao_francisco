<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fornecedores = Fornecedor::all();
        return view('fornecedor.index', compact('fornecedores'));
    }

    public function search(Request $request)
    {
        $term = $request->get('q');

        return Fornecedor::where('nome_fantasia', 'like', "%{$term}%")
            ->limit(20)
            ->get(['id', 'nome_fantasia']);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'razao_social' => 'required|string',
                'nome_fantasia' => 'nullable|string',
                'cnpj' => 'nullable|string',
                'endereco' => 'nullable|string',
                'inscricao_estadual' => 'nullable|string'
            ]);
            Fornecedor::create($request->all());
            return redirect()->route('fornecedor.index')->with('success', 'Fornecedor cadastrado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fornecedor $fornecedor)
    {
        try {
            $fornecedor = Fornecedor::findOrFail($fornecedor->id);
            $request->validate([
                'razao_social' => 'required|string',
                'nome_fantasia' => 'nullable|string',
                'cnpj' => 'nullable|string',
                'endereco' => 'nullable|string',
                'inscricao_estadual' => 'nullable|string'
            ]);
            $fornecedor->update($request->all());
            return redirect()->route('fornecedor.index')->with('success', 'Fornecedor atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor->id);
        $fornecedor->delete();
        return redirect()->route('fornecedor.index')->with('success', 'Fornecedor deletado com sucesso');
    }

    public function buscar(Request $request)
{
    $termo = $request->input('q');

    $fornecedores = Fornecedor::where('razao_social', 'LIKE', $termo . '%')
        ->orderBy('razao_social')
        ->limit(20)
        ->get(['id', 'razao_social']);

    return response()->json($fornecedores);
}
    public function busca(Request $request)
    {
        $termo = $request->query('q');

        if (empty($termo)) {
            return response()->json([]);
        }

        $fornecedores = Fornecedor::where('razao_social', 'LIKE', "%{$termo}%")
            ->orWhere('nome_fantasia', 'LIKE', "%{$termo}%")
            ->limit(10)
            ->get(['id', 'razao_social']);

        return response()->json($fornecedores);
    }

    /**
     * Retorna um fornecedor específico em formato JSON.
     * Usado para manter o valor do filtro após o recarregamento da página.
     */
    public function showJson(Fornecedor $fornecedor)
    {
        return response()->json([
            'id' => $fornecedor->id,
            'text' => $fornecedor->razao_social // O Select2 espera a chave 'text'
        ]);
    }

}
