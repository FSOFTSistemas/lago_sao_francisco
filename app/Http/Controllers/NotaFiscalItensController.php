<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotaFiscalItens;
use Illuminate\Http\Request;

class NotaFiscalItensController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try{
        $request->validate([
            'nota_fical_id' => 'required|exists:nota_fiscal,id',
            'produto_id'    => 'required|exists:produtos,id',
            'quantidade'    => 'required|numeric|min:0',
            'v_unitario'    => 'required|numeric|min:0',
            'desconto'      => 'nullable|numeric|min:0',
            'subtotal'      => 'required|numeric|min:0',
            'cst'           => 'required|string|max:10',
            'cfop_id'       => 'required|exists:cfop,id',
            'csosm'         => 'nullable|string|max:10',
            'total'         => 'required|numeric|min:0',
            'base_ICMS'     => 'nullable|numeric|min:0',
            'vICMS'         => 'nullable|numeric|min:0',
            'base_st'       => 'nullable|numeric|min:0',
            'vST'           => 'nullable|numeric|min:0',
        ]);
        NotaFiscalItens::created($request->all());
        return redirect()->route('nota_fiscal_itens.index')->with('sucess', 'Erro ao criar nota fiscal itens');
       }catch(\Exception $e){
        dd($e->getMessage());
        return redirect()->route('nota_fiscal_itens.index')->with('error', 'Erro ao criar nota fiscal itens');
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
        $notaFiscalItem = NotaFiscalItens::findOrFail($id);
        $request->validate([
                'nota_fical_id' => 'required|exists:nota_fiscal,id',
                'produto_id'    => 'required|exists:produtos,id',
                'quantidade'    => 'required|numeric|min:0',
                'v_unitario'    => 'required|numeric|min:0',
                'desconto'      => 'nullable|numeric|min:0',
                'subtotal'      => 'required|numeric|min:0',
                'cst'           => 'required|string|max:10',
                'cfop_id'       => 'required|exists:cfop,id',
                'csosm'         => 'nullable|string|max:10',
                'total'         => 'required|numeric|min:0',
                'base_ICMS'     => 'nullable|numeric|min:0',
                'vICMS'         => 'nullable|numeric|min:0',
                'base_st'       => 'nullable|numeric|min:0',
                'vST'           => 'nullable|numeric|min:0',
            ]);
            $notaFiscalItem->update($request->all);
            return redirect()->route('nota_fiscal_itens.index')->with('sucess', 'nota fiscal item atualizado com sucesso');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar nota fiscal item');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $notaFiscalItem = NotaFiscalItens::findOrFail($id);
            $notaFiscalItem.delete();
            return redirect()->route('nota_fiscal_item.index')->with('success', 'nota fiscal item excluído com sucesso!');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir nota fiscal item'); 
        }
    }
}
