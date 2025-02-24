<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use Illuminate\Http\Request;

class CaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caixas = Caixa::all();
        return view('caixas.index', compact('caixas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('caixas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([

                'descricao' => 'required|string',
                'valor_inicial' => 'nullable|numeric',
                'valor_final' => 'nullable|numeric',
                'data_abertura' => 'required|date',
                'data_fechamento' => 'nullable|date',
                'status' => 'required|in:aberto,fechado',
                'usuario_abertura_id' => 'required|exists:usuarios,id',
                'usuario_fechamento_id' => 'nullable|exists:usuarios,id',
                'observacoes' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            Caixa::create($request->all());
            return redirect()->route('caixas.index')->with('success', 'Caixa cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('caixas.index')->with('error', 'Erro ao cadastrar caixa!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Caixa $caixa)
    {
        $caixa = Caixa::findOrfail($caixa->id);
        return view('caixas.show', compact('caixa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Caixa $caixa)
    {
        $caixa = Caixa::findOrfail($caixa->id);
        return view('caixas.edit', compact('caixa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Caixa $caixa)
    {
        try {
            $caixa = Caixa::findOrfail($caixa->id);
            $request->validate([
                'descricao' => 'required|string',
                'valor_inicial' => 'nullable|numeric',
                'valor_final' => 'nullable|numeric',
                'data_abertura' => 'required|date',
                'data_fechamento' => 'nullable|date',
                'status' => 'required|in:aberto,fechado',
                'usuario_abertura_id' => 'required|exists:usuarios,id',
                'usuario_fechamento_id' => 'nullable|exists:usuarios,id',
                'observacoes' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $caixa->update($request->all());
            return redirect()->route('caixas.index')->with('success', 'Caixa atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('caixas.index')->with('error', 'Erro ao atualizar caixa!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caixa $caixa)
    {
        $caixa = Caixa::findOrfail($caixa->id);
        $caixa->delete();
        return redirect()->route('caixas.index')->with('success', 'Caixa deletado com sucesso!');
    }
}
