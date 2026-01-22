<?php

namespace App\Http\Controllers;

use App\Models\PreferenciasHotel;
use App\Models\Temporada;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PreferenciasHotelController extends Controller
{
    public function show()
    {
        $preferencia = PreferenciasHotel::first();
        $temporadas = Temporada::orderBy('data_inicio')->get();

        return view('preferencias.hotel', compact('preferencia', 'temporadas'));
    }

    public function store(Request $request)
    {
        // 1. Limpa a máscara de dinheiro ANTES da validação
        $this->prepararValores($request);

        $validated = $request->validate([
            'checkin_hora' => 'required|integer|min:0|max:23',
            'checkin_minuto' => 'required|integer|min:0|max:59',
            'checkout_hora' => 'required|integer|min:0|max:23',
            'checkout_minuto' => 'required|integer|min:0|max:59',
            'limpeza_quarto' => 'sometimes|accepted',
            'valor_diaria' => 'required|in:diaria,totalDiaria,tarifario',
            // Agora a validação 'numeric' vai passar
            'valor_pet_pequeno' => 'nullable|numeric|min:0',
            'valor_pet_medio'   => 'nullable|numeric|min:0',
            'valor_pet_grande'  => 'nullable|numeric|min:0',
        ]);

        $checkin = Carbon::createFromTime($validated['checkin_hora'], $validated['checkin_minuto']);
        $checkout = Carbon::createFromTime($validated['checkout_hora'], $validated['checkout_minuto']);

        // Como já limpamos no inicio, podemos usar o valor direto do request ou validated
        PreferenciasHotel::updateOrCreate(['id' => 1], [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'limpeza_quarto' => $request->has('limpeza_quarto'),
            'valor_diaria' => $validated['valor_diaria'],
            'valor_pet_pequeno' => $validated['valor_pet_pequeno'] ?? 0,
            'valor_pet_medio'   => $validated['valor_pet_medio'] ?? 0,
            'valor_pet_grande'  => $validated['valor_pet_grande'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Preferências salvas com sucesso!');
    }

    /**
     * Remove formatação de moeda (ex: 1.200,50 -> 1200.50)
     */
    private function prepararValores(Request $request)
    {
        $campos = ['valor_pet_pequeno', 'valor_pet_medio', 'valor_pet_grande'];
        $novosDados = [];

        foreach ($campos as $campo) {
            if ($request->filled($campo)) {
                $valor = $request->input($campo);
                // Remove ponto de milhar
                $valor = str_replace('.', '', $valor);
                // Troca vírgula decimal por ponto
                $valor = str_replace(',', '.', $valor);
                $novosDados[$campo] = $valor;
            }
        }

        // Sobrescreve os dados no request atual
        if (!empty($novosDados)) {
            $request->merge($novosDados);
        }
    }
}