<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\FluxoCaixa;
use App\Models\Movimento;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FluxoCaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = Auth::user();
        $movimento = Movimento::all();
        $empresa = Empresa::all();
        $caixa = Caixa::all();
        $planoDeContas = PlanoDeConta::all();
        $fluxoCaixas = FluxoCaixa::all();
        return view('fluxoCaixa.index', compact('fluxoCaixas', 'movimento', 'empresa', 'planoDeContas', 'users', 'caixa' ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fluxoCaixa.create');
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida',
                'movimento_id' => 'required|exists:movimentos,id', 
                'caixa_id' => 'required|exists:caixas,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor_total' => 'required|numeric',
                'plano_de_conta_id' => 'nullable|exists:plano_de_contas,id'
            ]);
            $request['usuario_id'] = Auth::user()->id;
            FluxoCaixa::create($request->all());
            return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('fluxoCaixa.index')->with('error', 'Erro ao cadastrar fluxo de caixa!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        return view('fluxoCaixa.show', compact('fluxoCaixa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        return view('fluxoCaixa.edit', compact('fluxoCaixa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FluxoCaixa $fluxoCaixa)
    {
        try {
            $fluxoCaixa= FluxoCaixa::findOrFail($fluxoCaixa->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida',
                'movimento_id' => 'required|exists:movimentos,id', 
                'caixa_id' => 'required|exists:caixas,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor_total' => 'required|numeric',
                'plano_de_conta_id' => 'nullable|exists:plano_de_contas,id'
            ]);
            $request['usuario_id'] = Auth::user()->id;
            $fluxoCaixa->update($request->all());
            return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('fluxoCaixa.index')->with('error', 'Erro ao atualizar fluxo de caixa!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FluxoCaixa $fluxoCaixa)
    {
        $fluxoCaixa = FluxoCaixa::findOrFail($fluxoCaixa->id);
        $fluxoCaixa->delete();
        return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa excluído com sucesso!');
    }
}
