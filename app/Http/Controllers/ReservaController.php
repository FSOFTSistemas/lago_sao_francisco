<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['quarto', 'hospede'])->latest()->paginate(10);
        return view('reserva.index', compact('reservas'));
    }

    public function create(Request $request)
{
    $checkin = $request->data_checkin;
    $checkout = $request->data_checkout;

    $quartos = Quarto::query();

    if ($checkin && $checkout) {
        $ocupados = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout])
                  ->orWhereBetween('data_checkout', [$checkin, $checkout])
                  ->orWhere(function ($query) use ($checkin, $checkout) {
                      $query->where('data_checkin', '<=', $checkin)
                            ->where('data_checkout', '>=', $checkout);
                  });
        })->pluck('quarto_id');

        $quartos = $quartos->whereNotIn('id', $ocupados);
    }

    $quartos = $quartos->get();
    $hospedes = Hospede::all();
    $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

    return view('reserva.create', compact('quartos', 'hospedes', 'hospedeBloqueado', 'checkin', 'checkout'));
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

    public function quartosDisponiveis(Request $request)
{
    $checkin = Carbon::createFromFormat('d/m/Y', $request->checkin);
    $checkout = Carbon::createFromFormat('d/m/Y', $request->checkout);
    $reservaId = $request->reserva_id; // pode ser null

    $quartosIndisponiveis = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout->copy()->subDay()])
                  ->orWhereBetween('data_checkout', [$checkin->copy()->addDay(), $checkout])
                  ->orWhere(function ($query) use ($checkin, $checkout) {
                      $query->where('data_checkin', '<', $checkin)
                            ->where('data_checkout', '>', $checkout);
                  });
        })
        ->when($reservaId, function ($query, $reservaId) {
            $query->where('id', '!=', $reservaId);
        })
        ->pluck('quarto_id');

    $quartosDisponiveis = Quarto::whereNotIn('id', $quartosIndisponiveis)->get();

    return response()->json($quartosDisponiveis);
}




}