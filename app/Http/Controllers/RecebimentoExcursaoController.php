<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecebimentoExcursaoRequest;
use App\Models\Excursao;
use App\Models\Log as LogSistema;
use App\Models\RecebimentoExcursao;
use App\Services\ExcursaoCaixaService;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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

                if ($request->boolean('iniciar_apos_recebimento')) {
                    $totalRecebidoAtualizado = (float) $excursaoTravada->recebimentos()
                        ->whereNotNull('fluxo_caixa_id')
                        ->whereNull('fluxo_cancelamento_id')
                        ->sum('valor');

                    if ($totalRecebidoAtualizado + 0.01 < (float) $excursaoTravada->total) {
                        throw new DomainException('É necessário quitar o saldo da excursão antes de iniciá-la.');
                    }

                    if ($excursaoTravada->status !== Excursao::STATUS_AGENDADO) {
                        throw new DomainException('Somente excursões agendadas podem ser iniciadas.');
                    }

                    if (! $excursaoTravada->data->isToday()) {
                        throw new DomainException(
                            'A excursão só pode ser iniciada na data agendada: '.$excursaoTravada->data->format('d/m/Y').'.',
                        );
                    }

                    $excursaoTravada->update([
                        'status' => Excursao::STATUS_EM_ANDAMENTO,
                        'iniciada_em' => now(),
                    ]);
                }
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

        $mensagem = $request->boolean('iniciar_apos_recebimento')
            ? 'Saldo recebido, lançado no caixa e excursão iniciada com sucesso!'
            : 'Recebimento registrado e lançado no caixa com sucesso!';

        return redirect()
            ->route('eventos.excursoes.index')
            ->with('success', $mensagem);
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

    public function recibo(RecebimentoExcursao $recebimento): Response|RedirectResponse
    {
        $recebimento->load([
            'excursao',
            'formaPagamento',
            'fluxoCancelamento',
        ]);

        if (! $recebimento->fluxo_caixa_id) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'Este pagamento ainda não possui movimentação de caixa para emissão do recibo.');
        }

        if ($recebimento->fluxo_cancelamento_id) {
            return redirect()
                ->route('eventos.excursoes.index')
                ->with('error', 'Não é possível emitir recibo de um pagamento estornado.');
        }

        $excursao = $recebimento->excursao;
        $empresa = Auth::user()?->empresa;
        $emitidoEm = now();

        $pdf = Pdf::loadView('eventos.excursoes.recibo_pagamento', compact(
            'recebimento',
            'excursao',
            'empresa',
            'emitidoEm',
        ))->setPaper('a5', 'landscape');
        $conteudo = $pdf->output();

        LogSistema::create([
            'tipo_acao' => 'Vizualizou',
            'descricao' => 'Emitiu recibo do pagamento #'.$recebimento->id
                .' da excursão #'.$excursao->id
                .' | Valor: R$ '.number_format((float) $recebimento->valor, 2, ',', '.')
                .' | Forma: '.($recebimento->formaPagamento?->descricao ?? 'Não informada')
                .' | Fluxo ID: '.$recebimento->fluxo_caixa_id,
            'usuario_id' => Auth::id(),
            'data_hora' => $emitidoEm,
        ]);

        $nomeArquivo = 'RECIBO_PAGAMENTO_'.str_pad((string) $recebimento->id, 6, '0', STR_PAD_LEFT).'.pdf';

        return response($conteudo, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nomeArquivo.'"',
        ]);
    }
}
