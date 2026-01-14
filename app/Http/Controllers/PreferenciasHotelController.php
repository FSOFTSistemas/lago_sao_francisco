<?php

namespace App\Http\Controllers;

use App\Models\PreferenciasHotel;
use App\Models\Temporada; // Importante: Adicionar o Model Temporada
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PreferenciasHotelController extends Controller
{
    public function show()
    {
        $preferencia = PreferenciasHotel::first();
        
        // --- BUSCA AS TEMPORADAS ---
        $temporadas = Temporada::orderBy('data_inicio')->get();

        // Passa 'temporadas' para a view junto com 'preferencia'
        return view('preferencias.hotel', compact('preferencia', 'temporadas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'checkin_hora' => 'required|integer|min:0|max:23',
            'checkin_minuto' => 'required|integer|min:0|max:59',
            'checkout_hora' => 'required|integer|min:0|max:23',
            'checkout_minuto' => 'required|integer|min:0|max:59',
            'limpeza_quarto' => 'sometimes|accepted',
            'valor_diaria' => 'required|in:diaria,totalDiaria,tarifario',
        ]);

        $checkin = Carbon::createFromTime($validated['checkin_hora'], $validated['checkin_minuto']);
        $checkout = Carbon::createFromTime($validated['checkout_hora'], $validated['checkout_minuto']);

        PreferenciasHotel::updateOrCreate(['id' => 1], [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'limpeza_quarto' => $request->has('limpeza_quarto'),
            'valor_diaria' => $validated['valor_diaria'],
        ]);

        return redirect()->back()->with('success', 'Preferências salvas com sucesso!');
    }
}