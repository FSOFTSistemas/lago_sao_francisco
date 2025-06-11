<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $vendedores = Vendedor::all();
            return response()->json($vendedores);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao listar vendedores.'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'nullable|email|unique:vendedors,email',
                'telefone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:vendedors,cpf',
                'endereco' => 'nullable|string|max:255',
            ],[
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode exceder 255 caracteres.',
                'email.email' => 'O email deve ser um endereço de email válido.',
                'email.unique' => 'Este email já está em uso.',
                'telefone.string' => 'O telefone deve ser um texto.',
                'telefone.max' => 'O telefone não pode exceder 20 caracteres.',
                'cpf.string' => 'O CPF deve ser um texto.',
                'cpf.max' => 'O CPF não pode exceder 14 caracteres.',
                'cpf.unique' => 'Este CPF já está em uso.',
                'endereco.string' => 'O endereço deve ser um texto.',
                'endereco.max' => 'O endereço não pode exceder 255 caracteres.',
            ]);

            Vendedor::create($validated);

            return redirect()->route('vendedores.index')->with('success', 'Vendedor criado com sucesso.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('vendedores.index')->with('error', 'Erro de validação ao criar vendedor.');
        } catch (\Exception $e) {
            return redirect()->route('vendedores.index')->with('error', 'Erro ao criar vendedor.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendedor $vendedor)
    {
        try {
            return response()->json($vendedor);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao exibir vendedor.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendedor $vendedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendedor $vendedor)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'nullable|email|unique:vendedors,email,' . $vendedor->id,
                'telefone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:vendedors,cpf,' . $vendedor->id,
                'endereco' => 'nullable|string|max:255',
            ],[
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode exceder 255 caracteres.',
                'email.email' => 'O email deve ser um endereço de email válido.',
                'email.unique' => 'Este email já está em uso.',
                'telefone.string' => 'O telefone deve ser um texto.',
                'telefone.max' => 'O telefone não pode exceder 20 caracteres.',
                'cpf.string' => 'O CPF deve ser um texto.',
                'cpf.max' => 'O CPF não pode exceder 14 caracteres.',
                'cpf.unique' => 'Este CPF já está em uso.',
                'endereco.string' => 'O endereço deve ser um texto.',
                'endereco.max' => 'O endereço não pode exceder 255 caracteres.',
            ]);

            $vendedor->update($validated);

            return redirect()->route('vendedores.index')->with('success', 'Vendedor atualizado com sucesso.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('vendedores.index')->with('error', 'Erro de validação ao atualizar vendedor.');
        } catch (\Exception $e) {
            return redirect()->route('vendedores.index')->with('error', 'Erro ao atualizar vendedor.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendedor $vendedor)
    {
        try {
            $vendedor->delete();
            return redirect()->route('vendedores.index')->with('success', 'Vendedor excluído com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('vendedores.index')->with('error', 'Erro ao excluir vendedor.');
        }
    }
}
