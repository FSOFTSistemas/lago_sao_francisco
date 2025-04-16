<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $empresas = Empresa::all();
    $enderecos = Endereco::all();

    $empresaId = session('empresa_id');

    $funcionarios = Funcionario::query()
        ->when($empresaId, function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })
        ->get();

    return view('funcionario.index', compact('funcionarios', 'empresas', 'enderecos'));
}


    public function create()
    {
        $empresas = Empresa::all();
        $enderecos = Endereco::all();
        return view('funcionario.form', compact('empresas', 'enderecos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string',
                'cpf' => 'required|string',
                'endereco_id' => 'nullable|exists:enderecos,id',
                'salario' => 'required|numeric',
                'data_contratacao' => 'required|date',
                'status' => 'required|in:ativo,inativo',
                'setor' => 'required|string',
                'cargo' => 'required|string',
                'empresa_id' => 'required|exists:empresas,id'
            ]);
            Funcionario::create($request->all());
            return redirect()->route('funcionario.index')->with('success', 'Funcionário criado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Funcionario $funcionario)
    {
        try {
            $funcionario = Funcionario::findOrFail($funcionario->id);
            $request->validate([
                'nome' => 'required|string',
                'cpf' => 'required|string',
                'endereco_id' => 'nullable|exists:enderecos,id',
                'salario' => 'required|numeric',
                'data_contratacao' => 'required|date',
                'status' => 'required|in:ativo,inativo',
                'setor' => 'required|string',
                'cargo' => 'required|string',
                'empresa_id' => 'required|exists:empresas,id'
            ]);
            $funcionario->update($request->all());
            return redirect()->route('funcionario.index')->with('success', 'Funcionário atualizado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    public function edit(Funcionario $funcionario)
    {
        $funcionario = Funcionario::findOrFail($funcionario->id);
        $empresas = Empresa::all();
        $enderecos = Endereco::all();
        return view('funcionario.form', compact('funcionario', 'empresas', 'enderecos'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Funcionario $funcionario)
    {
        try {
            $funcionario = Funcionario::findOrFail($funcionario->id);
            $funcionario->delete();
            return redirect()->route('funcionario.index')->with('success', 'Funcionário deletado com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar funcionário');
        }
    }
}
