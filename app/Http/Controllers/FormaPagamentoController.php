<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormaPagamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formaPagamento = FormaPagamento::daEmpresa(Auth::user()->empresa_id)->get();
        return view('preferencias.formaPagamento', compact('formaPagamento'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao' => 'required|string|max:255',
            ]);

            $validated['empresa_id'] = Auth::user()->empresa_id;
            FormaPagamento::create($validated);
            return redirect()->route('formaPagamento.index')->with('success', 'Registro criado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'descricao' => 'required|string|max:255',
            ]);

            $formaPagamento = FormaPagamento::find($id);

            $formaPagamento->update($validated);
            return redirect()->route('formaPagamento.index')->with('success', 'Registro atualizado com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $formaPagamento = FormaPagamento::find($id);
            $formaPagamento->delete();
            return redirect()->route('formaPagamento.index')->with('success', 'Registro excluído com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao validar dados');
        }
    }
}
