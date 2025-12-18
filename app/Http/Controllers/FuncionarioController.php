<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\Funcionario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FuncionarioController extends Controller
{
    public $cargos = ['Auxiliar de Manutenção', 'Eletricista', 'Jardineiro', 'Serviços Gerais', 'Vigia', 'Assistente Administrativo', 'Porteiro', 'Tratador de Animais', 'Cozinheiro Geral', 'Assistente de Vendas', 'Camareira de Hotel', 'Recepcionista em Geral', 'Garçom', 'Lavadeira em Geral', 'Chefe de Portaria', 'Coordenador Administrativo', 'Gerente Administrativo', 'Analista Financeiro'];

    public $setores = ['Administrativo', 'Produção', 'Manutenção', 'Gerência'];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = Empresa::all();
        $enderecos = Endereco::all();
        // In your controller
        $setores = $this->setores;
        $cargos = $this->cargos;



        $empresaId = session('empresa_id');

        $funcionarios = Funcionario::query()
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->get();

        return view('funcionario.index', compact('funcionarios', 'empresas', 'enderecos', 'setores', 'cargos'));
    }


    public function create()
    {
        $empresas = Empresa::all();
        $enderecos = Endereco::all();
        $setores = $this->setores;
        $cargos = $this->cargos;
        $roles = Role::all();
        $users = User::where('ativo', true)->with('roles')->get();
        $permissoes = Permission::all();
        return view('funcionario.form', compact('empresas', 'enderecos', 'setores', 'cargos', 'roles', 'users', 'permissoes'));
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
                'empresa_id' => 'required|exists:empresas,id',
                'vendedor' => 'boolean',
                'caixa' => 'boolean',
                'senha_supervisor' =>  'nullable|string|min:6',
                'email' => 'nullable|email|unique:users',
                'password' => 'nullable|string|min:6',
                'role' => 'nullable|exists:roles,name',
                'permissoes' => 'array',
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
                'empresa_id.exists' => 'A empresa selecionada é inválida.',

                'senha_supervisor.string' => 'A senha deve ser um texto.',
                'senha_supervisor.min' => 'A senha deve ter no mínimo 6 caracteres.',
            ]);

            // Formatar CPF antes de salvar (remover máscara)
            if ($validatedData['cpf']) {
                $validatedData['cpf'] = preg_replace('/[^0-9]/', '', $validatedData['cpf']);
            }
            if ($validatedData['senha_supervisor']) {
                $validatedData['senha_supervisor'] = bcrypt($validatedData['senha_supervisor']);
            }


            $funcionario = Funcionario::create([
                'nome' => $validatedData['nome'],
                'cpf' => $validatedData['cpf'],
                'endereco_id' => $validatedData['endereco_id'],
                'salario' => $validatedData['salario'],
                'data_contratacao' => $validatedData['data_contratacao'],
                'status' => $validatedData['status'],
                'setor' => $validatedData['setor'],
                'cargo' => $validatedData['cargo'],
                'empresa_id' => $validatedData['empresa_id'],
                'vendedor' => $validatedData['vendedor'],
                'caixa' => $validatedData['caixa'],
                'senha_supervisor' => $validatedData['senha_supervisor'],
            ]);

            if ($request->filled('criar_usuario')) {
                $usuario = User::create([
                    'name' => $validatedData['nome'],
                    'email' => $validatedData['email'],
                    'password' => bcrypt($validatedData['password']),
                    'empresa_id' => $validatedData['empresa_id'],
                ]);
                $usuario->assignRole($validatedData['role']);

                if (!empty($validatedData['permissoes'])) {
                    $permissoes = Permission::whereIn('id', $validatedData['permissoes'])->get();
                    $usuario->givePermissionTo($permissoes);
                }
                if ($funcionario->caixa) {
                    Caixa::create([
                        'descricao' => 'Caixa de ' . $funcionario->nome,
                        'data_abertura' => now(),
                        'data_fechamento' => now(),
                        'status' => 'fechado',
                        'empresa_id' => $funcionario->empresa_id,
                        'usuario_id' => $usuario->id,
                        'usuario_abertura_id' => $usuario->id,
                    ]);
                }
            }
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
                'salario' => 'nullable|numeric',
                'data_contratacao' => 'required|date',
                'status' => 'required|in:ativo,inativo',
                'setor' => 'required|string',
                'cargo' => 'required|string',
                'empresa_id' => 'required|exists:empresas,id',
                'vendedor' => 'boolean',
                'caixa' => 'boolean',
                'senha_supervisor' => 'nullable|string|min:6',
            ]);
            
            $dados = $request->all();

            // Lógica de senha do supervisor
            if ($request->input('setor') === 'Gerência') {
                if ($request->filled('senha_supervisor')) {
                    // SE PREENCHEU SENHA: CRIPTOGRAFA ANTES DE SALVAR
                    $dados['senha_supervisor'] = bcrypt($request->input('senha_supervisor'));
                } else {
                    // SE NÃO PREENCHEU: REMOVE DO ARRAY PARA NÃO APAGAR A SENHA ATUAL
                    unset($dados['senha_supervisor']);
                }
            } else {
                // SE MUDOU DE SETOR (SAIU DA GERÊNCIA): LIMPA A SENHA
                $dados['senha_supervisor'] = null;
            }

            // Tratamento do CPF (apenas números)
            if (isset($dados['cpf'])) {
                $dados['cpf'] = preg_replace('/[^0-9]/', '', $dados['cpf']);
            }

            // Checkboxes não enviados (false)
            $dados['vendedor'] = $request->has('vendedor');
            $dados['caixa'] = $request->has('caixa');

            $funcionario->update($dados);
            
            return redirect()->route('funcionario.index')->with('success', 'Funcionário atualizado com sucesso');
        } catch (\Exception $e) {
            // Em produção, evite dd()
            // dd($e)->getMessage(); 
            return redirect()->back()->with('error', 'Erro ao validar dados: ' . $e->getMessage());
        }
    }

    public function edit(Funcionario $funcionario)
    {
        $funcionario = Funcionario::findOrFail($funcionario->id);
        $empresas = Empresa::all();
        $enderecos = Endereco::all();
        $setores = $this->setores;
        $cargos = $this->cargos;
        $roles = Role::all();
        $permissoes = Permission::all();
        return view('funcionario.form', compact('funcionario', 'empresas', 'enderecos','roles', 'setores', 'cargos', 'permissoes'));
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

    public function search(Request $request)
    {
        $term = $request->get('q');

        return Funcionario::where('vendedor', true)
            ->where('nome', 'like', "%{$term}%")
            ->limit(20)
            ->get(['id', 'nome']);
    }
}
