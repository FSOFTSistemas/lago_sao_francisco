<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use App\Models\Categoria;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with(['tarifas' => function($q) {
            $q->orderBy('ativo', 'desc')->orderBy('nome');
        }])->orderBy('posicao')->get();

        return view('tarifa.tarifa', compact('categorias'));
    }

    public function create(Request $request)
    {
        $categoriaIdPreSelecionada = $request->get('categoria_id');
        $categorias = Categoria::where('status', 1)->orderBy('titulo')->get();
        return view('tarifa.manageTarifa', compact('categorias', 'categoriaIdPreSelecionada'));
    }

    public function store(Request $request)
    {
        try {
            $this->prepararDadosMonetarios($request);

            $request->validate([
                'nome' => 'required|string',
                'ativo' => 'required|boolean',
                'observacoes' => 'nullable|string',
                'categoria_id' => 'required|exists:categorias,id',
                'alta_temporada' => 'boolean',
                'data_inicio' => 'nullable|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
                'padrao_adultos' => 'nullable|integer',
                'padrao_criancas' => 'nullable|integer',
                'adicional_adulto' => 'nullable|numeric',
                'adicional_crianca' => 'nullable|numeric',
                // Dias da semana
                'seg' => 'nullable|numeric', 'ter' => 'nullable|numeric', 'qua' => 'nullable|numeric',
                'qui' => 'nullable|numeric', 'sex' => 'nullable|numeric', 'sab' => 'nullable|numeric',
                'dom' => 'nullable|numeric'
            ]);

            // Pega todos os dados já higienizados pelo merge
            $dados = $request->all();
            
            // Garante o booleano (checkbox não enviado vira false)
            $dados['alta_temporada'] = $request->has('alta_temporada');

            Tarifa::create($dados);

            return redirect()->route('tarifa.index')->with('success', 'Tarifa criada com sucesso');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro ao criar tarifa: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $tarifa = Tarifa::findOrFail($id);
        $categorias = Categoria::where('status', 1)->orderBy('titulo')->get();
        return view('tarifa.manageTarifa', compact('tarifa', 'categorias'));
    }

    public function update(Request $request, Tarifa $tarifa)
    {
        try {
            // Converte valores monetários antes de validar
            $this->prepararDadosMonetarios($request);

            $request->validate([
                'nome' => 'required|string',
                'ativo' => 'required|boolean',
                'observacoes' => 'nullable|string',
                'categoria_id' => 'required|exists:categorias,id',
                'alta_temporada' => 'boolean',
                'data_inicio' => 'nullable|date',
                'data_fim' => 'nullable|date|after_or_equal:data_inicio',
                'padrao_adultos' => 'nullable|integer',
                'padrao_criancas' => 'nullable|integer',
                'adicional_adulto' => 'nullable|numeric',
                'adicional_crianca' => 'nullable|numeric',
                'seg' => 'nullable|numeric', 'ter' => 'nullable|numeric', 'qua' => 'nullable|numeric',
                'qui' => 'nullable|numeric', 'sex' => 'nullable|numeric', 'sab' => 'nullable|numeric',
                'dom' => 'nullable|numeric'
            ]);

            $dados = $request->all();
            $dados['alta_temporada'] = $request->has('alta_temporada');

            $tarifa->update($dados);

            return redirect()->route('tarifa.index')->with('success', 'Tarifa Atualizada com sucesso');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar tarifa: ' . $e->getMessage());
        }
    }

    public function destroy(Tarifa $tarifa)
    {
        try {
            $tarifa->delete();
            return redirect()->route('tarifa.index')->with('success', 'Tarifa deletada com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar tarifa');
        }
    }

    /**
     * Função auxiliar para converter moeda BRL (1.000,00) para US (1000.00)
     * e injetar de volta no Request antes da validação.
     */
    private function prepararDadosMonetarios(Request $request)
    {
        $camposMonetariosTexto = ['adicional_adulto', 'adicional_crianca'];
        $novosDados = [];

        foreach ($camposMonetariosTexto as $campo) {
            if ($request->filled($campo)) {
                $valor = $request->input($campo);
                // Remove ponto de milhar e troca vírgula por ponto
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
                $novosDados[$campo] = $valor;
            }
        }

        // Merge para sobrescrever os dados originais no request
        if (!empty($novosDados)) {
            $request->merge($novosDados);
        }
    }
}