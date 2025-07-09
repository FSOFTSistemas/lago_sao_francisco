<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $cliente= Cliente::all();
        $enderecos = Endereco::all();
        $empresa = Empresa::all();
        return view('cliente.index', compact('cliente', 'enderecos', 'empresa', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $enderecos = Endereco::all();
        $empresa = Empresa::all();
        return view('cliente.create', compact('enderecos', 'empresa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            
            $rules = [
                'nome_razao_social' => 'required|string',
                'apelido_nome_fantasia' => 'required|string',
                'telefone' => 'nullable|string',
                'whatsapp' => 'nullable|string',
                'data_nascimento' => 'nullable|date',
                'endereco_id' => 'nullable|exists:enderecos,id',
                'empresa_id' => 'nullable|exists:empresa,id',
                'tipo' => 'required|in:PF,PJ'
            ];

            // Validação condicional para CPF/CNPJ
            if ($request->tipo == 'PJ') {
                $rules['cpf_cnpj'] = 'required|string'; // CNPJ obrigatório para PJ
                $rules['rg_ie'] = 'nullable|string'; // IE opcional para PJ
            } else {
                $rules['cpf_cnpj'] = 'nullable|string'; // CPF opcional para PF
                $rules['rg_ie'] = 'nullable|string'; // RG opcional para PF
            }

            $request->validate($rules);
            
            $request['empresa_id'] = Auth::user()->empresa_id;
            Cliente::create($request->all());
            
            return redirect()->route('cliente.index')->with('success', 'Cliente criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao validar dados: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $enderecos = Endereco::all();
        $cliente = Cliente::findOrFail($cliente->id);
        return view('cliente.create', compact('cliente', 'enderecos'));
    }

    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, Cliente $cliente)
        {
            try {
                $cliente = Cliente::findOrFail($cliente->id);
                
                $rules = [
                    'nome_razao_social' => 'required|string',
                    'apelido_nome_fantasia' => 'required|string',
                    'telefone' => 'nullable|string',
                    'whatsapp' => 'nullable|string',
                    'data_nascimento' => 'nullable|date',
                    'endereco_id' => 'nullable|exists:enderecos,id',
                    'tipo' => 'required|in:PF,PJ'
                ];

                // Validação condicional para CPF/CNPJ
                if ($request->tipo == 'PJ') {
                    $rules['cpf_cnpj'] = 'required|string'; // CNPJ obrigatório para PJ
                    $rules['rg_ie'] = 'nullable|string'; // IE opcional para PJ
                } else {
                    $rules['cpf_cnpj'] = 'nullable|string'; // CPF opcional para PF
                    $rules['rg_ie'] = 'nullable|string'; // RG opcional para PF
                }

                $request->validate($rules);
                
                $cliente->update($request->all());
                return redirect()->route('cliente.index')->with('success', 'Cliente atualizado com sucesso!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erro ao validar dados: ' . $e->getMessage());
            }
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        try {
            $cliente = Cliente::findOrFail($cliente->id);
            $cliente->delete();
            return redirect()->route('cliente.index')->with('success', 'Cliente deletado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar cliente');
        }
    }

    public function search(Request $request)
{
    $term = $request->get('q');

    return Cliente::where('nome_razao_social', 'like', "%{$term}%")
        ->limit(20)
        ->get(['id', 'nome_razao_social']);
}



}
