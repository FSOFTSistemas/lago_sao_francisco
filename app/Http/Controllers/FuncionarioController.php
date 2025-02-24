<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Empresa;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function index()
    {
        $funcionarios = Funcionario::all();
        return view('funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        $empresas = Empresa::all();
        return view('funcionarios.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'endereco' => 'required|string',
            'salario' => 'required|string',
            'data_contratacao' => 'required|date',
            'status' => 'required|string',
            'setor' => 'required|string',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        Funcionario::create($request->all());
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit(Funcionario $funcionario)
    {
        $empresas = Empresa::all();
        return view('funcionarios.edit', compact('funcionario', 'empresas'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'endereco' => 'required|string',
            'salario' => 'required|string',
            'data_contratacao' => 'required|date',
            'status' => 'required|string',
            'setor' => 'required|string',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        $funcionario->update($request->all());
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Funcionario $funcionario)
    {
        $funcionario->delete();
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário excluído com sucesso!');
    }
}
