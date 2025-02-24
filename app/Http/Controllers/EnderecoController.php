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
        $validated = $request->validate([
            'logradouro' => 'required|string',
            'numero' => 'required|string',
            'bairro' => 'required|string',
            'uf' => 'required|string|size:2',
            'cidade' => 'required|string',
            'cep' => 'required|string|max:10',
            'ibge' => 'nullable|string',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        $endereco = Endereco::create($validated);

        return response()->json($endereco, 201);
    }

    public function show($id)
    {
        return response()->json(Endereco::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $endereco = Endereco::findOrFail($id);

        $validated = $request->validate([
            'logradouro' => 'sometimes|string',
            'numero' => 'sometimes|string',
            'bairro' => 'sometimes|string',
            'uf' => 'sometimes|string|size:2',
            'cidade' => 'sometimes|string',
            'cep' => 'sometimes|string|max:10',
            'ibge' => 'nullable|string',
            'empresa_id' => 'sometimes|exists:empresas,id',
        ]);

        $endereco->update($validated);

        return response()->json($endereco);
    }

    public function destroy($id)
    {
        $endereco = Endereco::findOrFail($id);
        $endereco->delete();

        return response()->json(['message' => 'Endereço deletado com sucesso']);
    }
}