<?php

namespace App\Http\Controllers;

use App\Models\DayUse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DayUseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dayuses = DayUse::all();
        return view('dayuse.index', compact('dayuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('dayuse._form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
public function show(DayUse $dayUse)
{
    // Carrega todos os relacionamentos necessários
    $dayUse->load([
        'cliente',
        'vendedor',
        'itens.item', // Assumindo que MovDayUse tem relacionamento 'item'
        'formaPag.formaPagamento' // Assumindo que DayUsePag tem relacionamento 'forma'
    ]);
    
    return view('dayuse.show', [
        'dayuse' => $dayUse,
        'valorPago' => $dayUse->formaPag->sum('valor'),
        'valorLiquido' => $dayUse->total + $dayUse->acrescimo - $dayUse->desconto
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DayUse $dayUse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DayUse $dayUse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $dayuse = DayUse::findOrFail($id);
            $dayuse->delete();
            return redirect()->route('dayuse.index')->with('success', 'Day Use deletado com sucesso!');
        } catch(\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar Day Use!', $e);
        }
    }
}
