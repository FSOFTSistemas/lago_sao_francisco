<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\NotaFiscal;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NotaFiscalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notasFiscais = NotaFiscal::all();
        
        $clientes= Cliente::all();
        $produtos = Produto::all();

        return view('NFe.index', compact('notasFiscais', 'produtos', 'clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return View('NFe.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'id' => 'required|integer',
                'cliente_id' => 'required|exists:clientes,id',
                'mcm_id' => 'required|exists:mcm,id',
                'cfop_id' => 'required|exists:cfop,id',
                'data' => 'required|date',
                'chave' => 'required|string',
                'numero' => 'required|integer',
                'serie' => 'nullable|string',
                'observacoes' => 'nullable|string',
                'info_complementares' => 'nullable|string',
                'peso_liquido' => 'nullable|numeric',
                'peso_bruto' => 'nullable|numeric',
                'pt_frete' => 'nullable|string',
                'pt_transporte' => 'nullable|string',
                'pt_nota' => 'nullable|string',
                'nfe_referenciavel' => 'nullable|string',
                'total_produtos' => 'nullable|numeric',
                'total_notas' => 'nullable|numeric',
                'total_desconto' => 'nullable|numeric',
                'outras_despesas' => 'nullable|numeric',
                'base_ICMS' => 'nullable|numeric',
                'vICMS' => 'nullable|numeric',
                'base_ST' => 'nullable|numeric',
                'vST' => 'nullable|numeric',
        ]);
        $request['empresa_id'] = Auth::user()->empresa_id;
        $request['usuario_id'] = Auth::user()->id;
        NotaFiscal::created($request->all());
        return redirect()->route('nota_fiscal.index')->with('sucess', 'Erro ao criar nota fiscal');
       }catch(\Exception $e){
        dd($e->getMessage());
        return redirect()->route('nota_fiscal.index')->with('error', 'Erro ao criar nota fiscal');
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
            $notaFiscal = NotaFiscal::findOrFail($id);
            $request->validate([
                    'id' => 'required|integer',
                    'cliente_id' => 'required|exists:clientes,id',
                    'mcm_id' => 'required|exists:mcm,id',
                    'cfop_id' => 'required|exists:cfop,id',
                    'data' => 'required|date',
                    'chave' => 'required|string',
                    'numero' => 'required|integer',
                    'serie' => 'nullable|string',
                    'observacoes' => 'nullable|string',
                    'info_complementares' => 'nullable|string',
                    'peso_liquido' => 'nullable|numeric',
                    'peso_bruto' => 'nullable|numeric',
                    'pt_frete' => 'nullable|string',
                    'pt_transporte' => 'nullable|string',
                    'pt_nota' => 'nullable|string',
                    'nfe_referenciavel' => 'nullable|string',
                    'total_produtos' => 'nullable|numeric',
                    'total_notas' => 'nullable|numeric',
                    'total_desconto' => 'nullable|numeric',
                    'outras_despesas' => 'nullable|numeric',
                    'base_ICMS' => 'nullable|numeric',
                    'vICMS' => 'nullable|numeric',
                    'base_ST' => 'nullable|numeric',
                    'vST' => 'nullable|numeric',
            ]);
            $request['empresa_id'] = Auth::user()->empresa_id;
            $request['usuario_id'] = Auth::user()->id;
            $notaFiscal->update($request->all());
            return redirect()->route('nota_fiscal.index')->with('sucess', 'nota fiscal atualizada com sucesso');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar nota fiscal');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $notaFiscal = NotaFiscal::findOrFail($id);
            $notaFiscal->delete();
            return redirect()->route('nota_fiscal.index')->with('success', 'nota fiscal excluída com sucesso!');
        }catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir nota fiscal'); 
        }
    }

    public function getEmpresaCurrent(){
        return Empresa::where('id', Auth::user()->empresa_id)->get();
    }

    
}
