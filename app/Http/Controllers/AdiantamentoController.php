<?php

namespace App\Http\Controllers;

use App\Models\Adiantamento;
use App\Models\Empresa;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class AdiantamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = Empresa::all();
        $funcionarios = Funcionario::all();
        $adiantamentos = Adiantamento::all();
        return view('adiantamento.index', compact('adiantamentos', 'empresas', 'funcionarios'));
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
                'status' => 'required|in:pendente,finalizado'
            ]);
            $request['empresa_id'] = Funcionario::findOrFail($request->funcionario_id)->empresa_id;
            Adiantamento::create([
                'valor' => $request->valor,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'funcionario_id' => $request->funcionario_id,
                'status' => $request->status
            ]);
            return redirect()->route('adiantamentos.index')->with('success', 'Adiantamento cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('adiantamentos.index')->with('error', 'Erro ao cadastrar adiantamento!');
        }
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
                'status' => 'required|in:pendente,finalizado',
            ]);
            $adiantamento->update([
                'valor' => $request->valor,
                'data' => $request->data,
                'descricao' => $request->descricao,
                'funcionario_id' => $request->funcionario_id,
                'status' => $request->status,
            ]);
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('adiantamento.index')->with('error', 'Erro ao atualizar adiantamento!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adiantamento $adiantamento)
    {
        try {
            $adiantamento->delete();
            return redirect()->route('adiantamento.index')->with('success', 'Adiantamento deletado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('adiantamento.index')->with('error', 'Erro ao deletar adiantamento!');
        }
    }
}
