<?php

namespace App\Http\Controllers;

use App\Models\Quarto;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class QuartoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all();
        $quartos = Quarto::all();
        return view('quarto.index', compact('categorias', 'quartos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        return view('quarto.createQuarto', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'nome' => 'required|string',
                'descricao' => 'nullable|string',
                'categoria_id' => 'required|exists:categorias,id',
                'status' => 'required|boolean',
            ]);
    
            if (empty($request->posicao)) {
                $lastPosition = Quarto::max('posicao'); 
                $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1; 
            } else {
                $validatedData['posicao'] = $request->posicao;
            }
            Quarto::create($validatedData);
    
            return redirect()->route('quarto.index')->with('success', 'Quarto criado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar o quarto');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quarto $quarto)
    {
        $quarto = Quarto::findOrFail($quarto->id);
        $categorias = Categoria::all();
        return view('quarto.createQuarto', compact('categorias', 'quarto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quarto $quarto)
    {
        try{
        $validatedData = $request->validate([
            'nome' => 'required|string',
            'descricao' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'status' => 'required|boolean',
        ]);
        $quarto = Quarto::findOrFail($quarto->id);
        if (empty($request->posicao)) {
            $lastPosition = Quarto::max('posicao'); 
            $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1; 
        } else {
            $validatedData['posicao'] = $request->posicao;
        }
        $quarto->update($validatedData);

            return redirect()->route('quarto.index')->with('success', 'Quarto atualizado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o quarto');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quarto $quarto)
    {
        try {
            $quarto = Quarto::findOrFail($quarto->id);
            $quarto->delete();
            return redirect()->route('quarto.index')->with('success', 'Quarto deletado com sucesso');
        } catch (\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar o quarto');
        }
    }
}
