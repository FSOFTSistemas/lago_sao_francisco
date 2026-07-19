<?php

namespace App\Http\Controllers;

use App\Models\Hospede;
use App\Models\Motorhome;
use Illuminate\Http\Request;

class MotorhomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $motorhomes = Motorhome::with('proprietario')
            ->when($request->filled('busca'), function ($query) use ($request) {
                $query->where('placa', 'like', '%'.$request->busca.'%')
                    ->orWhere('modelo', 'like', '%'.$request->busca.'%');
            })
            ->orderBy('placa')
            ->get();

        return view('motorhome.index', compact('motorhomes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hospedes = Hospede::orderBy('nome')->get();

        return view('motorhome.create', compact('hospedes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'placa' => 'required|string|max:10|unique:motorhomes,placa',
                'modelo' => 'nullable|string',
                'comprimento' => 'nullable|numeric',
                'cor' => 'nullable|string',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'observacoes' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            $motorhome = Motorhome::create($validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'motorhome' => $motorhome]);
            }

            return redirect()->route('motorhome.index')->with('success', 'Motorhome cadastrado com sucesso!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', 'Erro ao cadastrar motorhome: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Motorhome $motorhome)
    {
        $hospedes = Hospede::orderBy('nome')->get();

        return view('motorhome.create', compact('motorhome', 'hospedes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Motorhome $motorhome)
    {
        try {
            $validated = $request->validate([
                'placa' => 'required|string|max:10|unique:motorhomes,placa,'.$motorhome->id,
                'modelo' => 'nullable|string',
                'comprimento' => 'nullable|numeric',
                'cor' => 'nullable|string',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'observacoes' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]);

            $motorhome->update($validated);

            return redirect()->route('motorhome.index')->with('success', 'Motorhome atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar motorhome: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Motorhome $motorhome)
    {
        try {
            $motorhome->delete();

            return redirect()->route('motorhome.index')->with('success', 'Motorhome removido com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover motorhome: '.$e->getMessage());
        }
    }
}
