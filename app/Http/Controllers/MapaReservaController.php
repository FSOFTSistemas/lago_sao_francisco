<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quarto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MapaReservaController extends Controller
{
    public function index(Request $request)
{
    $inicio = Carbon::parse($request->input('inicio', now()->startOfMonth()->toDateString()));
    
    $fim = $inicio->copy()->endOfMonth();

    $datas = collect();
    for ($date = $inicio->copy(); $date->lte($fim); $date->addDay()) {
        $datas->push($date->copy());
    }

    $quartos = Quarto::with(['reservas' => function ($q) use ($inicio, $fim) {
        $q->whereDate('data_checkout', '>=', $inicio)
          ->whereDate('data_checkin', '<=', $fim);
    }])->get();

    return view('reserva.mapa', compact('quartos', 'datas'));
}
}
