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
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|unique:funcionarios,cpf|max:14',
            'endereco_id' => 'nullable|exists:enderecos,id',
            'salario' => 'nullable|numeric|min:0',
            'data_contratacao' => 'required|date|before_or_equal:today',
            'status' => 'required|in:ativo,inativo',
            'setor' => 'required|string|max:100',
            'cargo' => 'required|string|max:100',
            'empresa_id' => 'required|exists:empresas,id'
        ], [
            
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome não pode ter mais que 255 caracteres.',
            
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cpf.max' => 'O CPF deve ter no máximo 14 caracteres.',
            
            'endereco_id.exists' => 'O endereço selecionado é inválido.',
            
            'salario.numeric' => 'O salário deve ser um valor numérico.',
            'salario.min' => 'O salário não pode ser negativo.',
            
            'data_contratacao.required' => 'A data de contratação é obrigatória.',
            'data_contratacao.date' => 'Informe uma data válida.',
            'data_contratacao.before_or_equal' => 'A data de contratação não pode ser futura.',
            
            'setor.required' => 'O setor é obrigatório.',
            'setor.string' => 'O setor deve ser um texto.',
            'setor.max' => 'O setor não pode ter mais que 100 caracteres.',
            
            'cargo.required' => 'O cargo é obrigatório.',
            'cargo.string' => 'O cargo deve ser um texto.',
            'cargo.max' => 'O cargo não pode ter mais que 100 caracteres.',
            
            'empresa_id.required' => 'A empresa é obrigatória.',
            'empresa_id.exists' => 'A empresa selecionada é inválida.'
        ]);

        // Formatar CPF antes de salvar (remover máscara)
        if($validatedData['cpf']){
            $validatedData['cpf'] = preg_replace('/[^0-9]/', '', $validatedData['cpf']);
        }
        Funcionario::create($validatedData);
        
        return redirect()->route('funcionario.index')->with('success', 'Funcionário criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao validar dados: ' . $e->getMessage());
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
            dd($e)->getMessage();
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
