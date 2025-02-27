<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;

class EnderecoController extends Controller
{
    public function index()
    {
        return response()->json(Endereco::all());
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'logradouro' => 'required|string',
                'numero' => 'required|string',
                'bairro' => 'required|string',
                'uf' => 'required|string|size:2',
                'cidade' => 'required|string',
                'cep' => 'required|string|max:10',
                'ibge' => 'nullable|string',
            ]);
    
            $endereco = Endereco::create($validated);
    
            return response()->json($endereco, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

       
    }

    public function update(Request $request, $id)
    {
        try {
            $endereco = Endereco::findOrFail($id);

            $validated = $request->validate([
                'logradouro' => 'sometimes|string',
                'numero' => 'sometimes|string',
                'bairro' => 'sometimes|string',
                'uf' => 'sometimes|string|size:2',
                'cidade' => 'sometimes|string',
                'cep' => 'sometimes|string|max:10',
                'ibge' => 'nullable|string',
            ]);
    
            $endereco->update($validated);
    
            return response()->json($endereco);


        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }

    }

    public function destroy($id)
    {
        try {
            $endereco = Endereco::findOrFail($id);
            $endereco->delete();

            return response()->json(['message' => 'Endereço deletado com sucesso']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}