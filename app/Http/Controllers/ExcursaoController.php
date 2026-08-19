<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExcursaoController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Excursao::STATUS, true)
            ? $request->query('status')
            : null;

        $query = Excursao::query()
            ->when($status, fn ($query) => $query->where('status', $status));

        $excursoes = (clone $query)
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $resumo = [
            'total' => (clone $query)->count(),
            'pessoas' => (clone $query)->sum('qtd_pessoas'),
            'valor' => (clone $query)->sum('valor'),
        ];

        return view('eventos.excursoes.index', compact('excursoes', 'resumo', 'status'));
    }

    public function create(): View
    {
        return view('eventos.excursoes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        Excursao::create($validated);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão cadastrada com sucesso!');
    }

    public function edit(Excursao $excursao): View
    {
        return view('eventos.excursoes.create', compact('excursao'));
    }

    public function update(Request $request, Excursao $excursao): RedirectResponse
    {
        $excursao->update($this->validateRequest($request));

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão atualizada com sucesso!');
    }

    public function destroy(Excursao $excursao): RedirectResponse
    {
        $excursao->delete();

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão excluída com sucesso!');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate(
            [
                'data' => ['required', 'date'],
                'qtd_pessoas' => ['required', 'integer', 'min:1'],
                'valor' => ['required', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(Excursao::STATUS)],
                'responsavel' => ['required', 'string', 'max:255'],
                'telefone_responsavel' => ['required', 'string', 'max:20'],
                'descricao' => ['required', 'string', 'max:1000'],
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
                'status.required' => 'Informe o status da excursão.',
                'status.in' => 'Informe um status válido para a excursão.',
                'responsavel.required' => 'Informe o responsável pela excursão.',
                'responsavel.max' => 'O nome do responsável deve ter no máximo 255 caracteres.',
                'telefone_responsavel.required' => 'Informe o telefone do responsável.',
                'telefone_responsavel.max' => 'O telefone deve ter no máximo 20 caracteres.',
                'descricao.required' => 'Informe a descrição da excursão.',
                'descricao.max' => 'A descrição deve ter no máximo 1000 caracteres.',
            ],
        );
    }
}
