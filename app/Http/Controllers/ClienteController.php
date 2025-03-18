<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Endereco;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cliente = Cliente::all();
        $endereco = Endereco::all();
        $empresa = Empresa::all();
        return view('cliente.index', compact('cliente', 'endereco', 'empresa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $endereco = Endereco::all();
        $empresa = Empresa::all();
        return view('cliente.create', compact('endereco', 'empresa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request -> validate([
                'nome_razao_social' => 'required|string',
                'apelido_nome_fantasia' => 'required|string',
                'telefone' => 'nullable|string',
                'whatsapp' => 'nullable|string',
                'data_nascimento' => 'nullable|date',
                'endereco_id' => 'nullable|exists:endereco,id',
                'cpf_cnpj' => 'required|string',
                'rg_ie' => 'nullable|string',
                'empresa_id' => 'nullable|exists:empresa,id',
                'tipo' => 'required|in:PF, PJ'
            ]);
            Cliente::create($request->all());
            return redirect()->route('cliente.index')->with('success', 'Cliente criado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $cliente = Cliente::findOrFail($cliente->id);
        return view('cliente.create', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        try{
            $cliente = Cliente::findOrFail($cliente->id);
            $request->validate([
            'nome_razao_social' => 'required|string',
            'apelido_nome_fantasia' => 'required|string',
            'telefone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'data_nascimento' => 'nullable|date',
            'endereco_id' => 'nullable|exists:endereco,id',
            'cpf_cnpj' => 'required|string',
            'rg_ie' => 'nullable|string',
            'empresa_id' => 'nullable|exists:empresa,id',
            'tipo' => 'required|in:PF, PJ'
            ]);
            $cliente->update($request->all());
            return redirect()->route('cliente.index')->with('success', 'Cliente atualizado com sucesso!'); 
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
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
}
