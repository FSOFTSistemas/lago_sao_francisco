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
        return view('tarifa.tarifa', compact('tarifa'));
    }

    public function create()
    {
        return view('tarifa.manageTarifa');
    }

   public function store(Request $request)
{
    try {
        $request->validate([
            'nome' => 'required|string',
            'ativo' => 'required|boolean',
            'observacoes' => 'string|nullable',
            'categoria_id' => 'required|exists:categorias,id',
            'seg' => 'numeric|nullable',
            'ter' => 'numeric|nullable',
            'qua' => 'numeric|nullable',
            'qui' => 'numeric|nullable',
            'sex' => 'numeric|nullable',
            'sab' => 'numeric|nullable',
            'dom' => 'numeric|nullable',
        ]);

        $dados = $request->except(['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom']);
        $camposDias = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];

        foreach ($camposDias as $dia) {
            $valor = $request->$dia;
            if (!is_null($valor)) {
                // Aceita vírgula ou ponto, e garante duas casas decimais
                $valorFormatado = number_format((float) str_replace(',', '.', $valor), 2, '.', '');
                $dados[$dia] = $valorFormatado;
            } else {
                $dados[$dia] = null;
            }
        }

        Tarifa::create($dados);

        return redirect()->route('tarifa.index')
                         ->with('success', 'Tarifa criada com sucesso');
    } catch (\Exception $e) {
        dd($e->getMessage());
        return redirect()->back()
                         ->with('error', 'Erro ao criar tarifa');
    }
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {   
        $tarifa = Tarifa::findOrFail($id);
        return view('tarifa.manageTarifa', compact('tarifa'));
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
            'seg' => 'numeric|nullable',
            'ter' => 'numeric|nullable',
            'qua' => 'numeric|nullable',
            'qui' => 'numeric|nullable',
            'sex' => 'numeric|nullable',
            'sab' => 'numeric|nullable',
            'dom' => 'numeric|nullable',
            // falta a tarifa_hospede
        ]);

        $dados = $request->except(['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom']);
        $camposDias = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];

        foreach ($camposDias as $dia) {
            $valor = $request->$dia;
            if (!is_null($valor)) {
                $valorFormatado = number_format((float) str_replace(',', '.', $valor), 2, '.', '');
                $dados[$dia] = $valorFormatado;
            } else {
                $dados[$dia] = null;
            }
        }

        $tarifa->update($dados);

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
