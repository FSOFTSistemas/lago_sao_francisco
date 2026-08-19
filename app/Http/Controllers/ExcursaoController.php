<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExcursaoController extends Controller
{
    public function create(): View
    {
        return view('eventos.excursoes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'data' => ['required', 'date'],
                'qtd_pessoas' => ['required', 'integer', 'min:1'],
                'valor' => ['required', 'numeric', 'min:0'],
            ],
            [
                'data.required' => 'Informe a data da excursão.',
                'data.date' => 'Informe uma data válida.',
                'qtd_pessoas.required' => 'Informe a quantidade de pessoas.',
                'qtd_pessoas.integer' => 'A quantidade de pessoas deve ser um número inteiro.',
                'qtd_pessoas.min' => 'A excursão deve ter pelo menos uma pessoa.',
                'valor.required' => 'Informe o valor da excursão.',
                'valor.numeric' => 'Informe um valor válido.',
                'valor.min' => 'O valor não pode ser negativo.',
            ],
        );

        Excursao::create($validated);

        return redirect()
            ->route('eventos.excursoes.create')
            ->with('success', 'Excursão cadastrada com sucesso!');
    }
}
