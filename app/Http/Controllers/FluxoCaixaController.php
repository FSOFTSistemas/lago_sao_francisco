<?php

namespace App\Http\Controllers;

use App\Models\FluxoCaixa;
use Illuminate\Http\Request;

class FluxoCaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fluxoCaixas = FluxoCaixa::all();
        return redirect()->route('fluxoCaixa.index', compact('fluxoCaixas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fluxoCaixa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'descricao' => 'required|string',
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'tipo' => 'required|in:entrada,saida',
                'movimento' => 'required|integer',
                'caixa_id' => 'required|exists:caixas,id',
                'usuario_id' => 'required|exists:usuarios,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor_total' => 'required|numeric',
            ]);
            FluxoCaixa::create($request->all());
            return redirect()->route('fluxoCaixa.index')->with('success', 'Fluxo de caixa cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
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
                'movimento' => 'required|integer',
                'caixa_id' => 'required|exists:caixas,id',
                'usuario_id' => 'required|exists:usuarios,id',
                'empresa_id' => 'required|exists:empresas,id',
                'valor_total' => 'required|numeric',
            ]);
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
