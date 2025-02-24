<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bancos = Banco::all();
        return view('bancos.index', compact('bancos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bancos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string',
            'agencia' => 'nullable|string',
            'numero_banco' => 'nullable|string',
            'numero_conta' => 'nullable|string',
            'digito_numero' => 'nullable|string',
            'digito_agencia' => 'nullable|string',
            'digito_conta' => 'nullable|string',
            'agencia_uf' => 'nullable|string',
            'agencia_cidade' => 'nullable|string',
            'taxa' => 'nullable|numeric',
        ]);
        Banco::create($request->all());
        return redirect()->route('bancos.index')->with('success', 'Banco cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banco $banco)
    {
        $banco = Banco::findOrFail($banco->id);
        return view('bancos.show', compact('banco'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banco $banco)
    {
        $banco = Banco::findOrFail($banco->id);
        return view('bancos.edit', compact('banco'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banco $banco)
    {
        try {
            $banco = Banco::findOrFail($banco->id);
        $request->validate([
            'descricao' => 'required|string',
            'agencia' => 'nullable|string',
            'numero_banco' => 'nullable|string',
            'numero_conta' => 'nullable|string',
            'digito_numero' => 'nullable|string',
            'digito_agencia' => 'nullable|string',
            'digito_conta' => 'nullable|string',
            'agencia_uf' => 'nullable|string',
            'agencia_cidade' => 'nullable|string',
            'taxa' => 'nullable|numeric',
        ]);
        $banco->update($request->all());
        } catch ( \Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('bancos.index')->with('error', 'Erro ao atualizar banco!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banco $banco)
    {
        $banco = Banco::findOrFail($banco->id);
        $banco->delete();
        return redirect()->route('bancos.index')->with('success', 'Banco excluído com sucesso!');
    }
}
