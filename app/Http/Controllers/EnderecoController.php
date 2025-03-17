<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Wavey\Sweetalert\Sweetalert;

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


            return response()->json([
                'success' => true,
                'endereco' => $endereco,
            ], 200); 

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


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

    public function buscarEnderecoPorCep($cep)
    {
        try {
            $cep = preg_replace('/[^0-9]/', '', $cep);

            if (strlen($cep) !== 8) {
                return response()->json(['error' => 'CEP inválido.'], 400);
            }

            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->failed()) {
                return response()->json(['error' => 'Não foi possível buscar o endereço.'], 500);
            }
            return $response->json();
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Sweetalert::error('Erro ao buscar endereco !', 'Error');
            redirect()->back()->withInput();
        }

    }
}
