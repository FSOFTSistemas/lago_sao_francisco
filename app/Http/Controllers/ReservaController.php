<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['quarto', 'hospede'])->latest()->paginate(10);
        return view('reserva.index', compact('reservas'));
    }

    public function create()
{
    $quartos = Quarto::all();
    $hospedes = Hospede::all();
    $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

    return view('reserva.create', compact('quartos', 'hospedes', 'hospedeBloqueado'));
}

    public function store(Request $request)
    {
        try {
            $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'required',
                'valor_total' => 'numeric',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
            ]);
    
            Reserva::create($request->all());
    
            return redirect()->route('reserva.index')->with('success', 'Reserva criada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao criar reserva!');
        }
    }


    public function edit(Reserva $reserva)
    {
        $quartos = Quarto::all();
        $hospedes = Hospede::all();
        return view('reserva.create', compact('reserva', 'quartos', 'hospedes'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        try {
        $request->validate([
            'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'required',
                'valor_total' => 'numeric',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
        ]);
        $reserva->update($request->all());

        return redirect()->back()->with('success', 'Reserva atualizada com sucesso!');
    } catch (\Exception $e) {
        dd($e->getMessage());
        return redirect()->back()->with('error', 'Erro ao atualizar reserva!');
        }
    }

    public function destroy(Reserva $reserva)
    {
        try {
        $reserva->delete();
        return redirect()->route('reserva.index')->with('success', 'Reserva removida com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao remover reserva!');
            }
    }
}
