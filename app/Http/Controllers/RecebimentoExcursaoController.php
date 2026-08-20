<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecebimentoExcursaoRequest;
use App\Models\Excursao;
use App\Models\RecebimentoExcursao;
use App\Services\ExcursaoCaixaService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RecebimentoExcursaoController extends Controller
{
    public function store(
        StoreRecebimentoExcursaoRequest $request,
        Excursao $excursao,
        ExcursaoCaixaService $excursaoCaixa,
    ): RedirectResponse {
        $dados = $request->validated();
        $comprovantePath = null;

        try {
            $caixa = $excursaoCaixa->caixaAbertoDoUsuario();

            DB::transaction(function () use ($request, $excursao, $excursaoCaixa, $caixa, $dados, &$comprovantePath) {
                $excursaoTravada = Excursao::query()->lockForUpdate()->findOrFail($excursao->id);
                if ($excursaoTravada->status === Excursao::STATUS_CANCELADO) {
                    throw new DomainException('Não é possível receber valores de uma excursão cancelada.');
                }

                $totalRecebido = (float) $excursaoTravada->recebimentos()
                    ->whereNotNull('fluxo_caixa_id')
                    ->whereNull('fluxo_cancelamento_id')
                    ->sum('valor');
                $saldo = max((float) $excursaoTravada->total - $totalRecebido, 0);
                if ((float) $dados['valor'] > $saldo + 0.01) {
                    throw new DomainException('O valor informado é maior que o saldo restante da excursão.');
                }

                if ($request->hasFile('comprovante')) {
                    $comprovantePath = $request->file('comprovante')
                        ->store("comprovantes/excursoes/{$excursaoTravada->id}", 'local');
                    if (! $comprovantePath) {
                        throw new DomainException('Não foi possível armazenar o comprovante.');
                    }
                }

                $recebimento = $excursaoTravada->recebimentos()->create([
                    'data_recebimento' => today(),
                    'valor' => $dados['valor'],
                    'forma_pagamento_id' => $dados['forma_pagamento_id'],
                    'comprovante_path' => $comprovantePath,
                ]);
                $excursaoCaixa->registrarRecebimento($recebimento, $caixa);
            });
        } catch (Throwable $exception) {
            if ($comprovantePath) {
                Storage::disk('local')->delete($comprovantePath);
            }

            if ($exception instanceof DomainException) {
                return redirect()
                    ->route('eventos.excursoes.index')
                    ->withInput()
                    ->with('error', $exception->getMessage());
            }

            throw $exception;
        }

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', 'Recebimento registrado e lançado no caixa com sucesso!');
    }

    public function comprovante(RecebimentoExcursao $recebimento): StreamedResponse
    {
        abort_unless(
            $recebimento->comprovante_path
                && Storage::disk('local')->exists($recebimento->comprovante_path),
            404,
            'Comprovante não encontrado.',
        );

        return Storage::disk('local')->download(
            $recebimento->comprovante_path,
            basename($recebimento->comprovante_path),
        );
    }
}
