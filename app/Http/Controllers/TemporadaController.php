<?php

namespace App\Http\Controllers;

use App\Models\Temporada;
use Illuminate\Http\Request;

class TemporadaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
            ], [
                'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.'
            ]);

            Temporada::create($validated);

            return redirect()->back()->with('success', 'Temporada criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar temporada: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $temporada = Temporada::findOrFail($id);

            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
            ]);

            $temporada->update($validated);

            return redirect()->back()->with('success', 'Temporada atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar temporada: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            $temporada->delete();

            return redirect()->back()->with('success', 'Temporada removida com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover temporada.');
        }
    }
}