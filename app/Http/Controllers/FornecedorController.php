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
        return view('fornecedores.index', compact('fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fornecedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'razao_social' => 'required|string',
                'nome_fantasia' => 'nullable|string',
                'cnpj' => 'nullable|string|max:14',
                'endereco' => 'nullable|string',
                'inscricao_estadual' => 'required|string'
            ]);
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fornecedor $fornecedor)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor->id);
        return view('fornecedores.show', compact('fornecedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fornecedor $fornecedor)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor->id);
        return view('fornecedores.edit', compact('fornecedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fornecedor $fornecedor)
    {
        try{
            $fornecedor = Fornecedor::findOrFail($fornecedor->id);
            $request->validate([
                'razao_social' => 'required|string',
                'nome_fantasia' => 'nullable|string',
                'cnpj' => 'nullable|string|max:14',
                'endereco' => 'nullable|string',
                'inscricao_estadual' => 'required|string'
            ]);
            $fornecedor->update($request->all());
            return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso');
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
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor deletado com sucesso');
    }
}
