<?php

namespace App\Http\Controllers;

use App\Models\PlanoDeConta;
use Illuminate\Http\Request;

class PlanoDeContaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $planosDeConta = PlanoDeConta::all();
        return redirect()->route('planoDeConta.index', compact('planosDeConta'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('planoDeConta.create', compact('planosDeConta'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|in:receita,despesa',
                'plano_de_conta_pai' => 'nullable|exists:planos_de_contas,id',
                'empresa_id' => 'required|exists:empresas,id'
            ]);
            PlanoDeConta::create($request->all());
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta criado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PlanoDeConta $planoDeConta)
    {
        $planoDeConta = PlanoDeConta::findOrFail($planoDeConta->id);
        return redirect()->route('planoDeConta.show', compact('planoDeConta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlanoDeConta $planoDeConta)
    {
        $planoDeConta = PlanoDeConta::findOrFail($planoDeConta->id);
        return view('planoDeConta.edit', compact('planoDeConta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlanoDeConta $planoDeConta)
    {
        try {
            $planoDeConta = PlanoDeConta::findOrFail($planoDeConta->id);
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|in:receita,despesa',
                'plano_de_conta_pai' => 'nullable|exists:planos_de_contas,id',
                'empresa_id' => 'required|exists:empresas,id'
            ]);
            $planoDeConta->update($request->all());
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlanoDeConta $planoDeConta)
    {
        $planoDeConta = PlanoDeConta::findOrFail($planoDeConta->id);
        $planoDeConta->delete();
        return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta deletado com sucesso');
    }
}
