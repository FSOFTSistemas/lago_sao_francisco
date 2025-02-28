<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enderecos = Endereco::all();
        $empresas = Empresa::all();
        return view('empresa.index', compact('empresas', 'enderecos'));
    }


    public function create(){
        {
            try {
                $enderecos = Endereco::all();
                return view('empresa.form', compact('enderecos'));
            } catch (\Exception $e) {
                dd($e->getMessage());
                return back()->with('error', 'Erro ao carregar o formulário de cadastro: ' . $e->getMessage());
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'razao_social' => 'required|string',
                'nome_fantasia' => 'nullable|string',
                'cnpj' => 'nullable|string|max:14',
                'inscricao_estadual' => 'required|string|max:9',
                'endereco_id' => 'nullable|exists:enderecos,id',
            ]);
            Empresa::create([
                'razao_social' => $request->razao_social,
                'nome_fantasia' => $request->nome_fantasia,
                'cnpj' => $request->cnpj,
                'inscricao_estadual' => $request->inscricao_estadual,
                'endereco_id' => $request->endereco_id,
            ]);
            return redirect()->route('empresa.index')->with('success', 'Empresa cadastrada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->route('empresa.index')->with('error', 'Erro ao cadastrar empresa!');
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Empresa $empresa)
    {
        $empresa = Empresa::findOrFail($empresa->id);
        return view('empresa.show', compact('empresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa)
    {
        $empresa = Empresa::findOrFail($empresa->id);
        return view('empresa.create', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {
        $empresa = Empresa::findOrFail($empresa->id);
        $request->validate([
            'razao_social' => 'required|string',
            'nome_fantasia' => 'nullable|string',
            'inscricao_estadual' => 'required|string|max:9',

        ]);

        $empresa->update($request->all());
        return redirect()->route('empresa.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa)
    {
        $empresa = Empresa::findOrFail($empresa->id);
        $empresa->delete();
        return redirect()->route('empresa.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
