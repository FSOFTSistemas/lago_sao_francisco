<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExcursaoRequest;
use App\Models\Excursao;
use App\Models\FormaPagamento;
use App\Services\ExcursaoFinanceiroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

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

        $queryPeriodo = Excursao::query()
            ->when($dataInicio, fn ($query) => $query->whereDate('data', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('data', '<=', $dataFim));

        $query = (clone $queryPeriodo)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($busca !== '', function ($query) use ($busca) {
                $codigo = ltrim($busca, '#');

                $query->where(function ($query) use ($busca, $codigo) {
                    $query->where('descricao', 'like', '%'.$busca.'%')
                        ->orWhere('responsavel', 'like', '%'.$busca.'%');

                    if (ctype_digit($codigo)) {
                        $query->orWhere('id', (int) $codigo);
                    }
                });
            });

        $excursoes = (clone $query)
            ->orderBy('data')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $resumo = [
            'media_pessoas_realizadas' => (int) floor((float) ((clone $query)
                ->where('status', Excursao::STATUS_REALIZADO)
                ->avg('qtd_pessoas') ?? 0)),
            'agendadas' => (clone $query)->where('status', Excursao::STATUS_AGENDADO)->count(),
            'realizadas' => (clone $query)->where('status', Excursao::STATUS_REALIZADO)->count(),
            'canceladas' => (clone $query)->where('status', Excursao::STATUS_CANCELADO)->count(),
            'pessoas' => (clone $query)
                ->where('status', Excursao::STATUS_REALIZADO)
                ->sum('qtd_pessoas'),
            'receita_prevista' => (clone $query)
                ->where('status', Excursao::STATUS_AGENDADO)
                ->sum('total'),
            'receita_realizada' => (clone $query)
                ->where('status', Excursao::STATUS_REALIZADO)
                ->sum('total'),
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
        $formasPagamento = FormaPagamento::query()->orderBy('descricao')->get();

        return view('eventos.excursoes.create', compact('formasPagamento'));
    }

    public function store(
        StoreExcursaoRequest $request,
        ExcursaoFinanceiroService $financeiro,
    ): RedirectResponse {
        $validated = $request->validated();
        $recebimentos = $validated['recebimentos'];
        unset($validated['recebimentos']);

        $validated['status'] = Excursao::STATUS_AGENDADO;
        $calculos = $financeiro->calcular(
            $validated,
            collect($recebimentos)->pluck('valor'),
        );
        $validated['total_almoco'] = $calculos['total_almoco'];
        $validated['subtotal'] = $calculos['subtotal'];
        $validated['total'] = $calculos['total'];

        $comprovantesSalvos = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $recebimentos,
                &$comprovantesSalvos,
            ) {
                $excursao = Excursao::create($validated);

                foreach ($recebimentos as $indice => $recebimento) {
                    $comprovantePath = null;

                    if ($request->hasFile("recebimentos.{$indice}.comprovante")) {
                        $comprovantePath = $request
                            ->file("recebimentos.{$indice}.comprovante")
                            ->store("comprovantes/excursoes/{$excursao->id}", 'local');

                        if (! $comprovantePath) {
                            throw new RuntimeException('Não foi possível armazenar o comprovante.');
                        }

                        $comprovantesSalvos[] = $comprovantePath;
                    }

                    $excursao->recebimentos()->create([
                        'data_recebimento' => today(),
                        'valor' => $recebimento['valor'],
                        'forma_pagamento_id' => $recebimento['forma_pagamento_id'],
                        'comprovante_path' => $comprovantePath,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($comprovantesSalvos as $comprovantePath) {
                Storage::disk('local')->delete($comprovantePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão cadastrada com sucesso!');
    }

    public function show(Excursao $excursao): View
    {
        return view('eventos.excursoes.create', [
            'excursao' => $excursao,
            'visualizacao' => true,
            'formasPagamento' => collect(),
        ]);
    }

    public function edit(Excursao $excursao): View|RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return redirect()->route('eventos.excursoes.show', $excursao);
        }

        return view('eventos.excursoes.create', [
            'excursao' => $excursao,
            'formasPagamento' => collect(),
        ]);
    }

    public function update(
        Request $request,
        Excursao $excursao,
        ExcursaoFinanceiroService $financeiro,
    ): RedirectResponse {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        $request->merge([
            'percentual_comissao' => $request->input('percentual_comissao', $excursao->percentual_comissao),
            'valor_almoco' => $request->input('valor_almoco', $excursao->valor_almoco),
            'qtd_almoco' => $request->input('qtd_almoco', $excursao->qtd_almoco),
            'acrescimo' => $request->input('acrescimo', $excursao->acrescimo),
            'desconto' => $request->input('desconto', $excursao->desconto),
        ]);
        $validated = $this->validateRequest($request);
        $dadosCalculo = array_merge($excursao->only([
            'valor_almoco',
            'qtd_almoco',
            'acrescimo',
            'desconto',
            'percentual_comissao',
        ]), $validated);
        $calculos = $financeiro->calcular($dadosCalculo, $excursao->recebimentos);
        $validated['total_almoco'] = $calculos['total_almoco'];
        $validated['subtotal'] = $calculos['subtotal'];
        $validated['total'] = $calculos['total'];

        $excursao->update($validated);

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão atualizada com sucesso!');
    }

    public function destroy(Excursao $excursao): RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        $excursao->update([
            'status' => Excursao::STATUS_CANCELADO,
            'cancelada_em' => now(),
        ]);

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

        $excursao->update([
            'status' => Excursao::STATUS_EM_ANDAMENTO,
            'iniciada_em' => now(),
        ]);

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

        $excursao->update([
            'status' => Excursao::STATUS_REALIZADO,
            'finalizada_em' => now(),
        ]);

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
                'valor_pessoa' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
                'percentual_comissao' => ['required', 'numeric', 'between:0,100'],
                'valor_almoco' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'qtd_almoco' => ['required', 'integer', 'min:0'],
                'acrescimo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'desconto' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
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
                'valor_pessoa.required' => 'Informe o valor por pessoa.',
                'valor_pessoa.numeric' => 'Informe um valor por pessoa válido.',
                'valor_pessoa.min' => 'O valor por pessoa deve ser maior que zero.',
                'valor_pessoa.max' => 'O valor por pessoa deve ser menor que R$ 100.000.000,00.',
                'percentual_comissao.between' => 'O percentual de comissão deve estar entre 0 e 100.',
                'qtd_almoco.integer' => 'A quantidade de almoços deve ser um número inteiro.',
                'qtd_almoco.min' => 'A quantidade de almoços não pode ser negativa.',
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
