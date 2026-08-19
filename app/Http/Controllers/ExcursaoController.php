<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExcursaoController extends Controller
{
    public function index(Request $request): View
    {
        $periodo = $request->validate(
            [
                'data_inicio' => ['nullable', 'date'],
                'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            ],
            [
                'data_inicio.date' => 'Informe uma data inicial válida.',
                'data_fim.date' => 'Informe uma data final válida.',
                'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            ],
        );

        $status = in_array($request->query('status'), Excursao::STATUS, true)
            ? $request->query('status')
            : null;
        $busca = trim((string) $request->query('busca', ''));
        $dataInicio = $periodo['data_inicio'] ?? null;
        $dataFim = $periodo['data_fim'] ?? null;

        $query = Excursao::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dataInicio, fn ($query) => $query->whereDate('data', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('data', '<=', $dataFim))
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('descricao', 'like', '%'.$busca.'%')
                        ->orWhere('responsavel', 'like', '%'.$busca.'%');
                });
            });

        $excursoes = (clone $query)
            ->orderBy('data')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $resumo = [
            'total' => (clone $query)->count(),
            'pessoas' => (clone $query)->sum('qtd_pessoas'),
            'valor' => (clone $query)->sum('valor'),
        ];

        return view('eventos.excursoes.index', compact(
            'excursoes',
            'resumo',
            'status',
            'busca',
            'dataInicio',
            'dataFim',
        ));
    }

    public function create(): View
    {
        return view('eventos.excursoes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated['status'] = Excursao::STATUS_AGENDADO;

        Excursao::create($validated);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão cadastrada com sucesso!');
    }

    public function show(Excursao $excursao): View
    {
        return view('eventos.excursoes.create', [
            'excursao' => $excursao,
            'visualizacao' => true,
        ]);
    }

    public function edit(Excursao $excursao): View|RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return redirect()->route('eventos.excursoes.show', $excursao);
        }

        return view('eventos.excursoes.create', compact('excursao'));
    }

    public function update(Request $request, Excursao $excursao): RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        $excursao->update($this->validateRequest($request));

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão atualizada com sucesso!');
    }

    public function destroy(Excursao $excursao): RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        $excursao->update(['status' => Excursao::STATUS_CANCELADO]);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão cancelada com sucesso!');
    }

    public function start(Excursao $excursao): RedirectResponse
    {
        if ($excursao->status !== Excursao::STATUS_AGENDADO) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'Somente excursões agendadas podem ser iniciadas.');
        }

        $excursao->update(['status' => Excursao::STATUS_EM_ANDAMENTO]);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão iniciada com sucesso!');
    }

    public function finish(Excursao $excursao): RedirectResponse
    {
        if ($excursao->status !== Excursao::STATUS_EM_ANDAMENTO) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'Somente excursões em andamento podem ser finalizadas.');
        }

        $excursao->update(['status' => Excursao::STATUS_REALIZADO]);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão finalizada com sucesso!');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate(
            [
                'data' => ['required', 'date'],
                'qtd_pessoas' => ['required', 'integer', 'min:1'],
                'valor' => ['required', 'numeric', 'min:0'],
                'responsavel' => ['required', 'string', 'max:255'],
                'telefone_responsavel' => ['required', 'string', 'max:20'],
                'descricao' => ['required', 'string', 'max:200'],
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
                'responsavel.required' => 'Informe o responsável pela excursão.',
                'responsavel.max' => 'O nome do responsável deve ter no máximo 255 caracteres.',
                'telefone_responsavel.required' => 'Informe o telefone do responsável.',
                'telefone_responsavel.max' => 'O telefone deve ter no máximo 20 caracteres.',
                'descricao.required' => 'Informe a descrição da excursão.',
                'descricao.max' => 'A descrição deve ter no máximo 200 caracteres.',
            ],
        );
    }

    private function isImmutable(Excursao $excursao): bool
    {
        return in_array($excursao->status, [
            Excursao::STATUS_REALIZADO,
            Excursao::STATUS_CANCELADO,
        ], true);
    }

    private function immutableExcursionRedirect(): RedirectResponse
    {
        return redirect()
            ->route('eventos.excursoes.index')
            ->with('error', 'Excursões realizadas ou canceladas estão disponíveis apenas para visualização.');
    }
}
