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
        
        // Busca todos e ordena via PHP convertendo para Inteiro para garantir ordem numérica (1, 2, 10...)
        // Isso resolve o problema de ordenação de string (1, 10, 2...)
        $quartos = Quarto::all()->sortBy(function($quarto) {
            return (int) $quarto->posicao;
        });

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
        try {
            $validatedData = $request->validate([
                'nome' => 'required|string',
                'descricao' => 'nullable|string',
                'categoria_id' => 'required|exists:categorias,id',
                'status' => 'required|boolean',
                'posicao' => 'nullable|integer|min:1',
            ]);
    
            if (empty($request->posicao)) {
                $lastPosition = Quarto::max('posicao'); 
                $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1; 
            } else {
                // Verifica conflito e ajusta posições existentes
                $this->ajustarConflitoPosicao($request->posicao);
                $validatedData['posicao'] = $request->posicao;
            }

            Quarto::create($validatedData);
    
            return redirect()->route('quarto.index')->with('success', 'Quarto criado com sucesso');
        } catch(\Exception $e){
            // dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar o quarto: ' . $e->getMessage());
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
        try {
            $validatedData = $request->validate([
                'nome' => 'required|string',
                'descricao' => 'nullable|string',
                'categoria_id' => 'required|exists:categorias,id',
                'status' => 'required|boolean',
                'posicao' => 'nullable|integer|min:1',
            ]);

            $quarto = Quarto::findOrFail($quarto->id);

            if (empty($request->posicao)) {
                // Se não informou, mantém a lógica de jogar pro final
                $lastPosition = Quarto::max('posicao'); 
                $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1; 
            } else {
                // Se a posição mudou, verifica conflitos
                if ($request->posicao != $quarto->posicao) {
                    $this->ajustarConflitoPosicao($request->posicao, $quarto->id);
                }
                $validatedData['posicao'] = $request->posicao;
            }

            $quarto->update($validatedData);

            return redirect()->route('quarto.index')->with('success', 'Quarto atualizado com sucesso');
        } catch(\Exception $e){
            // dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o quarto: ' . $e->getMessage());
        }
    }

    /**
     * Método auxiliar para evitar conflito de posições.
     * Empurra quartos existentes para frente se a posição desejada já estiver ocupada.
     */
    private function ajustarConflitoPosicao($novaPosicao, $ignorarId = null)
    {
        // Verifica se já existe algum quarto (diferente do atual) nesta posição
        $query = Quarto::where('posicao', $novaPosicao);
        
        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        if ($query->exists()) {
            // Incrementa a posição de todos os quartos que estão na posição desejada ou à frente
            // Isso cria um "espaço" para o novo quarto
            Quarto::where('posicao', '>=', $novaPosicao)
                ->when($ignorarId, function($q) use ($ignorarId) {
                    $q->where('id', '!=', $ignorarId);
                })
                ->increment('posicao');
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
            // dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar o quarto');
        }
    }
}