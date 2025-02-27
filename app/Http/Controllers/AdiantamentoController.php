<?php

namespace App\Http\Controllers;

use App\Models\Adiantamento;
use Illuminate\Http\Request;

class AdiantamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adiantamentos = Adiantamento::all();
        return view('adiantamentos.index', compact('adiantamentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('adiantamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'descricao' => 'required|string',
                'funcionario_id' => 'required|exists:funcionarios,id',
                'empresa_id' => 'required|exists:empresas,id',
                'status' => 'required|in:pendente,finalizado'
            ]);
            Adiantamento::create([
                'valor' => $request->valor,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'funcionario_id' => $request->funcionario_id,
                'empresa_id' => $request->empresa_id,
                'status' => $request->status
            ]);
            return redirect()->route('adiantamentos.index')->with('success', 'Adiantamento cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('adiantamentos.index')->with('error', 'Erro ao cadastrar adiantamento!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Adiantamento $adiantamento)
    {
        $adiantamento = Adiantamento::findOrFail($adiantamento->id);
        return view('adiantamentos.show', compact('adiantamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adiantamento $adiantamento)
    {
        $adiantamento = Adiantamento::findOrFail($adiantamento->id);
        return view('adiantamentos.edit', compact('adiantamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Adiantamento $adiantamento)
    {
        try {
            $request->validate([
                'valor' => 'required|numeric',
                'data' => 'required|date',
                'descricao' => 'required|string',
                'funcionario_id' => 'required|exists:funcionarios,id',
                'empresa_id' => 'required|exists:empresas,id',
                'status' => 'required|in:pendente,finalizado',
            ]);
            $adiantamento->update([
                'valor' => $request->valor,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'funcionario_id' => $request->funcionario_id,
                'empresa_id' => $request->empresa_id,
                'status' => $request->status,
            ]);
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('adiantamentos.index')->with('error', 'Erro ao atualizar adiantamento!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adiantamento $adiantamento)
    {
        $adiantamento->delete();
        return redirect()->route('adiantamentos.index')->with('success', 'Adiantamento deletado com sucesso!');
    }
}
