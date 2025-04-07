<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tarifa = Tarifa::all();
        return view('preferencias.tarifa', compact('tarifa'));
    }

    public function create()
    {
        return view('preferencias.manageTarifa');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string',
                'ativo' => 'required|boolean',
                'observacoes' => 'string|nullable',
                'categoria' => 'required|string',
                'seg' => 'decimal:2|nullable',
                'ter' => 'decimal:2|nullable',
                'qua' => 'decimal:2|nullable',
                'qui' => 'decimal:2|nullable',
                'sex' => 'decimal:2|nullable',
                'sab' => 'decimal:2|nullable',
                'dom' => 'decimal:2|nullable',
                // falta a tarifa_hospede
            ]);
            Tarifa::create($request->all());
            return redirect()->route('tarifa.index')->with('success', 'Tarifa criada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar tarifa');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tarifa $tarifa)
    {   
        $tarifa = Tarifa::all();
        return view('preferencias.manageTarifa', compact('tarifa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tarifa $tarifa)
    {
        try {
            $tarifa = Tarifa::findOrFail($tarifa->id);
            $request->validate([
                'nome' => 'required|string',
                'ativo' => 'required|boolean',
                'observacoes' => 'string|nullable',
                'categoria' => 'required|string',
                'seg' => 'decimal:2|nullable',
                'ter' => 'decimal:2|nullable',
                'qua' => 'decimal:2|nullable',
                'qui' => 'decimal:2|nullable',
                'sex' => 'decimal:2|nullable',
                'sab' => 'decimal:2|nullable',
                'dom' => 'decimal:2|nullable',
                // falta a tarifa_hospede
            ]);
            $tarifa->update($request->all());
            return redirect()->route('tarifa.index')->with('success', 'Tarifa Atualizada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar tarifa');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tarifa $tarifa)
    {
        try {
            $tarifa = Tarifa::findOrFail($tarifa->id);
            $tarifa->delete();
            return redirect()->route('tarifa.index')->with('success', 'Tarifa deletada com sucesso');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar tarifa');
        }
    }
}
