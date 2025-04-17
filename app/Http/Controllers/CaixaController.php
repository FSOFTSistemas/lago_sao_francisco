<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = Auth::user();
        $empresas = Empresa::all();
        $caixas = Caixa::all();
        return view('caixa.index', compact('caixas', 'empresas', 'users'));
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
                'observacoes' => 'nullable|string',
            ]);
            $request['empresa_id'] = Auth::user()->empresa_id;
            $request['usuario_abertura_id'] = Auth::user()->id;
            $request['usuario_fechamento_id'] = Auth::user()->id;
            Caixa::create($request->all());
            return redirect()->route('caixa.index')->with('success', 'Caixa cadastrado com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('caixa.index')->with('error', 'Erro ao cadastrar caixa!');
        }
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
                'observacoes' => 'nullable|string',
                'empresa_id' => 'required|exists:empresas,id',
            ]);
            $caixa->update($request->all());
            return redirect()->route('caixa.index')->with('success', 'Caixa atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('caixa.index')->with('error', 'Erro ao atualizar caixa!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caixa $caixa)
    {
        try {
            $caixa = Caixa::findOrfail($caixa->id);
            $caixa->delete();
            return redirect()->route('caixa.index')->with('success', 'Caixa deletado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->route('caixa.index')->with('error', 'Erro ao deletar caixa!');
        }
    }
}
