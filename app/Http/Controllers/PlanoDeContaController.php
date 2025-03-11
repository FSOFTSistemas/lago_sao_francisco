<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanoDeContaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $empresas = Empresa::all();
        $planoDeContas = PlanoDeConta::all();
        return view('planoDeConta.index', compact('planoDeContas', 'empresas', 'user'));
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
                'plano_de_conta_pai' => 'nullable|exists:plano_de_contas,id',

            ]);
            $request['empresa_id'] = Auth::user()->empresa_id;
            PlanoDeConta::create($request->all());
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta criado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'descricao' => 'required|string',
                'tipo' => 'required|in:receita,despesa',
                'plano_de_contas_pai' => 'nullable|exists:plano_de_contas,id',
            ]);
            $planoDeConta = PlanoDeConta::find($id);
            $planoDeConta->update([
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'plano_de_conta_pai' => $request->plano_de_conta_pai,
            ]);
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $planoDeConta = PlanoDeConta::find($id);
            $planoDeConta->delete();
            return redirect()->route('planoDeConta.index')->with('success', 'Plano de Conta deletado com sucesso');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao deletar Plano de Conta');
        }
    }
}
