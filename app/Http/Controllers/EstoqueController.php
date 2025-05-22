<?php

namespace App\Http\Controllers;
use App\Models\Estoque;
use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estoques = Estoque::all();
        return view('estoques.index', compact('estoques'));
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
        try{
            $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'estoque_atual' => 'required|numeric|min:0',
                'empresa_id' => 'required|exists:empresas,id',
                'entradas' => 'required|numeric|min:0',
                'saidas' => 'required|numeric|min:0',
            ]);
            Estoque::create($request->all());
            return redirect()->route('estoque.index')->with('sucess', 'Estoque criado com sucesso');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar estoque');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
           $estoque = Estoque::findOrFail($id);
           $request->validate([
                'produto_id' => 'required|exists:produtos,id',
                'estoque_atual' => 'required|numeric|min:0',
                'empresa_id' => 'required|exists:empresas,id',
                'entradas' => 'required|numeric|min:0',
                'saidas' => 'required|numeric|min:0',
            ]);
            $estoque->update($request->all);
            return redirect()->route('estoque.index')->with('sucess', 'Estoque atualiza com sucesso');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar estoque');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $estoque = Estoque::findOrFail($id);
            $estoque.delete();
            return redirect()->route('empresa.index')->with('success', 'estoque excluído com sucesso!');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir estoque'); 
        }
    }
}
