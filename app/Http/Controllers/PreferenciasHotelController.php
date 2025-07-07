<?php

namespace App\Http\Controllers;

use App\Models\PreferenciasHotel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreferenciasHotelController extends Controller
{
    public function show()
    {
        $preferencia = PreferenciasHotel::first();
        return view('preferencias.hotel', compact('preferencia'));
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

        $checkin = \Carbon\Carbon::createFromTime($validated['checkin_hora'], $validated['checkin_minuto']);
        $checkout = \Carbon\Carbon::createFromTime($validated['checkout_hora'], $validated['checkout_minuto']);

        PreferenciasHotel::updateOrCreate(['id' => 1], [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'limpeza_quarto' => $request->has('limpeza_quarto'),
            'valor_diaria' => $validated['valor_diaria'],
        ]);

        return redirect()->back()->with('success', 'Preferências salvas com sucesso!');
    }
}
