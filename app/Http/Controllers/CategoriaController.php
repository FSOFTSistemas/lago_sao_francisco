<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Controllers\Controller;
use App\Models\Tarifa;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{

    public function index()
    {
        $categorias = Categoria::withCount('quartos')->get();
        return view('categoria.index', compact('categorias'));
    }

    public function create()
    {
        return view('categoria.create');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'titulo' => 'required|string',
                'ocupantes' => 'required|string',
                'descricao' => 'nullable|string',
                'status' => 'required|boolean',
                'posicao' => 'nullable|string',
            ]);

            if (empty($validatedData['posicao'])) {
                $lastPosition = Categoria::max('posicao');
                $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1;
            }

            $categoria = Categoria::create($validatedData);

            Tarifa::create([
                'nome' => $categoria->titulo,
                'ativo' => true,
                'categoria_id' => $categoria->id,
            ]);

            return redirect()->route('categoria.index')->with('success', 'Categoria criada com sucesso!');
        } catch (\Exception $e) {
            dd($e)->getMessage();
            return redirect()->back()->with('error', 'Erro ao criar a categoria.');
        }
    }

    public function edit($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return view('categoria.create', compact('categoria'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao editar a categoria.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'titulo' => 'required|string',
                'ocupantes' => 'required|integer',
                'descricao' => 'nullable|string',
                'status' => 'required|boolean',
                'posicao' => 'nullable|integer',
            ]);

            $categoria = Categoria::findOrFail($id);

            if (empty($request->posicao)) {
                $lastPosition = Categoria::max('posicao');
                $validatedData['posicao'] = $lastPosition ? $lastPosition + 1 : 1;
            }

            $categoria->update($validatedData);

            return redirect()->route('categoria.index')->with('success', 'Categoria atualizada com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar a categoria.');
        }
    }

    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return redirect()->route('categoria.index')->with('success', 'Categoria deletada com sucesso!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar a categoria.');
        }
    }
}
