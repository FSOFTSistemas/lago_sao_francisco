<?php

namespace App\Http\Controllers;

use App\Models\Diaria;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;

class DiariaController extends Controller
{
    public function index()
    {
        $produtos = Produto::all();
        $diarias = Diaria::all();
        $clientes = Cliente::all();
        return view('diaria.index', compact('produtos', 'diarias', 'clientes'));
    }

    public function store(Request $request)
    {
        try {
            // dd($request);
            $request->validate([
                'valor' => 'required',
                'quantidade' => 'required',
                'cliente_id' => 'required|exists:clientes,id',
                'produto_id' => 'nullable|exists:produtos,id',
                'tipo' => 'required|in:day_use,passaporte',
            ]);
            Diaria::create($request->all());
            return redirect()->route('diaria.index')->with('success', 'Diaria criada com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar diaria!');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $diaria = Diaria::findOrFail($id);
            $request->validate([
                'valor' => 'required',
                'quantidade' => 'required',
                'cliente_id' => 'required|exists:clientes,id',
                'produto_id' => 'nullable|exists:produtos,id',
                'tipo' => 'required|in:day_use,passaporte',
            ]);
            $diaria->update($request->all());
            return redirect()->route('diaria.index')->with('success', 'Diaria atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar diaria!');
        }
    }

    public function destroy($id)
    {
        try {
            $diaria = Diaria::findOrFail($id);
            $diaria->delete();
            return redirect()->route('diaria.index')->with('success', 'Diaria excluída com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir diaria!');
        }
    }
}
