<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExcursaoRequest;
use App\Models\CardapioExcursao;
use App\Models\Excursao;
use App\Models\FormaPagamento;
use App\Services\ExcursaoCaixaService;
use App\Services\ExcursaoFinanceiroService;
use DomainException;
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
            ->with([
                'almoco',
                'recebimentos.formaPagamento',
            ])
            ->orderByRaw("CASE WHEN status IN ('REALIZADO', 'CANCELADO') THEN 1 ELSE 0 END")
            ->orderBy('data')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $posicaoFinanceira = (clone $query)
            ->where('status', '!=', Excursao::STATUS_CANCELADO)
            ->withSum([
                'recebimentos as total_recebido_caixa' => fn ($query) => $query
                    ->whereNotNull('fluxo_caixa_id')
                    ->whereNull('fluxo_cancelamento_id'),
            ], 'valor')
            ->get(['id', 'total']);

        $totalRecebido = round((float) $posicaoFinanceira->sum(
            fn (Excursao $excursao) => (float) ($excursao->total_recebido_caixa ?? 0),
        ), 2);
        $saldoAReceber = round((float) $posicaoFinanceira->sum(
            fn (Excursao $excursao) => max(
                (float) $excursao->total - (float) ($excursao->total_recebido_caixa ?? 0),
                0,
            ),
        ), 2);

        $formasPagamento = FormaPagamento::query()
            ->orderBy('descricao')
            ->get()
            ->reject(fn (FormaPagamento $forma) => str_contains(mb_strtolower($forma->descricao), 'crediário'))
            ->values();

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
            'saldo_a_receber' => $saldoAReceber,
            'total_recebido' => $totalRecebido,
        ];

        return view('eventos.excursoes.index', compact(
            'excursoes',
            'resumo',
            'status',
            'busca',
            'dataInicio',
            'dataFim',
            'formasPagamento',
        ));
    }

    public function create(): View
    {
        $formasPagamento = FormaPagamento::query()
            ->orderBy('descricao')
            ->get()
            ->reject(fn (FormaPagamento $forma) => str_contains(mb_strtolower($forma->descricao), 'crediário'))
            ->values();
        $cardapiosExcursao = CardapioExcursao::query()->where('ativo', true)->orderBy('nome')->get();

        return view('eventos.excursoes.create', compact('formasPagamento', 'cardapiosExcursao'));
    }

    public function store(
        StoreExcursaoRequest $request,
        ExcursaoFinanceiroService $financeiro,
        ExcursaoCaixaService $excursaoCaixa,
    ): RedirectResponse {
        $validated = $request->validated();
        $recebimentos = $validated['recebimentos'];
        unset($validated['recebimentos']);
        $dadosAlmoco = [
            'possui_almoco' => (bool) ($validated['possui_almoco'] ?? false),
            'cardapio_excursao_id' => $validated['cardapio_excursao_id'] ?? null,
            'quantidade' => $validated['almoco_quantidade'] ?? 0,
        ];
        unset($validated['possui_almoco'], $validated['cardapio_excursao_id'], $validated['almoco_quantidade']);
        $this->aplicarValoresAlmoco($validated, $dadosAlmoco);

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
            $caixa = $excursaoCaixa->caixaAbertoDoUsuario();

            DB::transaction(function () use (
                $request,
                $validated,
                $recebimentos,
                $dadosAlmoco,
                $caixa,
                $excursaoCaixa,
                &$comprovantesSalvos,
            ) {
                $excursao = Excursao::create($validated);
                $this->sincronizarAlmoco($excursao, $dadosAlmoco);

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

                    $recebimentoCriado = $excursao->recebimentos()->create([
                        'data_recebimento' => today(),
                        'valor' => $recebimento['valor'],
                        'forma_pagamento_id' => $recebimento['forma_pagamento_id'],
                        'comprovante_path' => $comprovantePath,
                    ]);

                    $excursaoCaixa->registrarRecebimento($recebimentoCriado, $caixa);
                }
            });
        } catch (Throwable $exception) {
            foreach ($comprovantesSalvos as $comprovantePath) {
                Storage::disk('local')->delete($comprovantePath);
            }

            if ($exception instanceof DomainException) {
                return back()
                    ->withInput()
                    ->with('error', $exception->getMessage());
            }

            throw $exception;
        }

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão cadastrada com sucesso!');
    }

    public function edit(Excursao $excursao): View|RedirectResponse
    {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        $excursao->loadMissing('almoco');
        $cardapioSelecionadoId = $excursao->almoco?->cardapio_excursao_id;
        if (! $cardapioSelecionadoId && $excursao->qtd_almoco > 0) {
            $cardapiosCompativeis = CardapioExcursao::query()
                ->where('valor_por_pessoa', $excursao->valor_almoco)
                ->limit(2)
                ->pluck('id');

            if ($cardapiosCompativeis->count() === 1) {
                $cardapioSelecionadoId = $cardapiosCompativeis->first();
            }
        }

        return view('eventos.excursoes.create', [
            'excursao' => $excursao,
            'formasPagamento' => collect(),
            'cardapioSelecionadoId' => $cardapioSelecionadoId,
            'cardapiosExcursao' => CardapioExcursao::query()
                ->where(fn ($query) => $query->where('ativo', true)
                    ->when($cardapioSelecionadoId, fn ($query) => $query->orWhere('id', $cardapioSelecionadoId)))
                ->orderBy('nome')
                ->get(),
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
            'percentual_comissao' => $request->filled('percentual_comissao') ? $request->input('percentual_comissao') : 0,
            'possui_almoco' => $request->has('possui_almoco')
                ? $request->boolean('possui_almoco')
                : ($excursao->almoco()->exists() || $excursao->qtd_almoco > 0),
            'valor_almoco' => $request->input('valor_almoco', $excursao->valor_almoco),
            'qtd_almoco' => $request->input('qtd_almoco', $excursao->qtd_almoco),
            'acrescimo' => $request->input('acrescimo', $excursao->acrescimo),
            'desconto' => $request->input('desconto', $excursao->desconto),
        ]);
        $validated = $this->validateRequest($request);
        $dadosAlmoco = [
            'possui_almoco' => (bool) ($validated['possui_almoco'] ?? false),
            'cardapio_excursao_id' => $validated['cardapio_excursao_id'] ?? null,
            'quantidade' => $validated['almoco_quantidade'] ?? 0,
        ];
        unset($validated['possui_almoco'], $validated['cardapio_excursao_id'], $validated['almoco_quantidade']);
        $this->aplicarValoresAlmoco($validated, $dadosAlmoco);
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

        DB::transaction(function () use ($excursao, $validated, $dadosAlmoco) {
            $excursao->update($validated);
            $this->sincronizarAlmoco($excursao, $dadosAlmoco);
        });

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Excursão atualizada com sucesso!');
    }

    public function destroy(
        Excursao $excursao,
        ExcursaoCaixaService $excursaoCaixa,
    ): RedirectResponse {
        if ($this->isImmutable($excursao)) {
            return $this->immutableExcursionRedirect();
        }

        try {
            DB::transaction(function () use ($excursao, $excursaoCaixa) {
                $excursao->loadMissing('recebimentos.formaPagamento');

                if ($excursao->recebimentos->contains(fn ($recebimento) => $recebimento->fluxo_caixa_id
                    && ! $recebimento->fluxo_cancelamento_id)) {
                    $caixa = $excursaoCaixa->caixaAbertoDoUsuario();
                    $excursaoCaixa->cancelarRecebimentos($excursao, $caixa);
                }

                $excursao->update([
                    'status' => Excursao::STATUS_CANCELADO,
                    'cancelada_em' => now(),
                ]);
            });
        } catch (DomainException $exception) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', $exception->getMessage());
        }

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

        if (! $excursao->data->isToday()) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'A excursão só pode ser iniciada na data agendada: '.$excursao->data->format('d/m/Y').'.');
        }

        $totalRecebido = (float) $excursao->recebimentos()
            ->whereNotNull('fluxo_caixa_id')
            ->whereNull('fluxo_cancelamento_id')
            ->sum('valor');
        if ($totalRecebido + 0.01 < (float) $excursao->total) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'Receba o saldo restante da excursão antes de iniciá-la.');
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
                'percentual_comissao' => ['nullable', 'numeric', 'between:0,100'],
                'valor_almoco' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'qtd_almoco' => ['required', 'integer', 'min:0'],
                'acrescimo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'desconto' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'responsavel' => ['required', 'string', 'max:255'],
                'telefone_responsavel' => ['required', 'string', 'max:20'],
                'descricao' => ['required', 'string', 'max:200'],
                'possui_almoco' => ['required', 'boolean'],
                'cardapio_excursao_id' => ['nullable', 'required_if:possui_almoco,1', 'integer', 'exists:cardapios_excursao,id'],
                'almoco_quantidade' => ['nullable', 'required_if:possui_almoco,1', 'integer', 'min:1'],
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
                'cardapio_excursao_id.required_if' => 'Selecione o cardápio do almoço.',
                'almoco_quantidade.required_if' => 'Informe a quantidade de almoços.',
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

    /** @param array{possui_almoco: bool, cardapio_excursao_id: mixed, quantidade: mixed} $dados */
    private function aplicarValoresAlmoco(array &$validated, array $dados): void
    {
        if (! $dados['possui_almoco']) {
            $validated['valor_almoco'] = 0;
            $validated['qtd_almoco'] = 0;

            return;
        }

        $cardapio = CardapioExcursao::findOrFail($dados['cardapio_excursao_id']);
        $validated['valor_almoco'] = $cardapio->valor_por_pessoa;
        $validated['qtd_almoco'] = (int) $dados['quantidade'];
    }

    /** @param array{possui_almoco: bool, cardapio_excursao_id: mixed, quantidade: mixed} $dados */
    private function sincronizarAlmoco(Excursao $excursao, array $dados): void
    {
        if (! $dados['possui_almoco']) {
            $excursao->almoco()->delete();

            return;
        }

        $cardapio = CardapioExcursao::findOrFail($dados['cardapio_excursao_id']);
        $quantidade = (int) $dados['quantidade'];
        $valorUnitario = (float) $cardapio->valor_por_pessoa;

        $excursao->almoco()->updateOrCreate(
            ['excursao_id' => $excursao->id],
            [
                'cardapio_excursao_id' => $cardapio->id,
                'nome_cardapio' => $cardapio->nome,
                'descricao_cardapio' => $cardapio->descricao_cardapio,
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'total' => round($quantidade * $valorUnitario, 2),
            ],
        );
    }

    private function immutableExcursionRedirect(): RedirectResponse
    {
        return redirect()
            ->route('eventos.excursoes.index')
            ->with('error', 'Excursões realizadas ou canceladas estão disponíveis apenas para visualização.');
    }
}
