<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hospede;
use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Http\Request;

class MapaQuartoController extends Controller
{
    public function index()
{
    $quartos = Quarto::with(['reservas.hospede'])->get();
    $hospede = Hospede::all();
    $reserva = Reserva::all();
    return view('mapaQuarto.index', compact('quartos', 'hospede', 'reserva'));
}

}
