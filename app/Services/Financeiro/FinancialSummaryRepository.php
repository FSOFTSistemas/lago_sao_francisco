<?php

namespace App\Services\Financeiro;

use App\Models\ContasAPagar;
use App\Models\ContasAReceber;
use App\Models\FluxoCaixa;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FinancialSummaryRepository
{
    public function receivables(int $companyId, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return ContasAReceber::query()
            ->where('empresa_id', $companyId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('data_recebimento', [$start->toDateString(), $end->toDateString()]);
            })
            ->get()
            ->map(fn (ContasAReceber $account) => [
                'id' => (int) $account->id,
                'description' => (string) $account->descricao,
                'value' => (float) $account->valor,
                'received' => (float) ($account->valor_recebido ?? 0),
                'status' => (string) $account->status,
                'due_date' => CarbonImmutable::parse($account->data_vencimento)->startOfDay(),
                'settled_at' => $account->data_recebimento
                    ? CarbonImmutable::parse($account->data_recebimento)->startOfDay()
                    : null,
            ]);
    }

    public function payables(int $companyId, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $accounts = ContasAPagar::query()
            ->where('empresa_id', $companyId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()])
                    ->orWhereHas('parcelas', fn ($installments) => $installments
                        ->whereBetween('data_vencimento', [$start->toDateString(), $end->toDateString()]));
            })
            ->with('parcelas')
            ->get();

        return $accounts->flatMap(function (ContasAPagar $account) use ($start, $end) {
            if ($account->parcelas->isEmpty()) {
                return [$this->normalizePayable(
                    (int) $account->id,
                    null,
                    (string) $account->descricao,
                    (float) $account->valor,
                    (float) ($account->valor_pago ?? 0),
                    (string) $account->status,
                    $account->data_vencimento,
                )];
            }

            return $account->parcelas
                ->filter(fn ($installment) => CarbonImmutable::parse($installment->data_vencimento)
                    ->betweenIncluded($start, $end))
                ->map(fn ($installment) => $this->normalizePayable(
                    (int) $account->id,
                    (int) $installment->id,
                    (string) $account->descricao,
                    (float) $installment->valor,
                    (float) ($installment->valor_pago ?? 0),
                    (string) $installment->status,
                    $installment->data_vencimento,
                ));
        })->values();
    }

    public function cashFlow(int $companyId, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return FluxoCaixa::query()
            ->where('empresa_id', $companyId)
            ->whereBetween('data', [$start->toDateString(), $end->toDateString()])
            ->whereIn('tipo', ['entrada', 'saida', 'cancelamento'])
            ->get()
            ->map(fn (FluxoCaixa $entry) => [
                'id' => (int) $entry->id,
                'type' => (string) $entry->tipo,
                'value' => (float) $entry->valor,
                'date' => CarbonImmutable::parse($entry->data)->startOfDay(),
            ]);
    }

    private function normalizePayable(
        int $accountId,
        ?int $installmentId,
        string $description,
        float $value,
        float $paid,
        string $status,
        mixed $dueDate,
    ): array {
        return [
            'account_id' => $accountId,
            'installment_id' => $installmentId,
            'description' => $description,
            'value' => $value,
            'paid' => $paid,
            'status' => $status,
            'due_date' => CarbonImmutable::parse($dueDate)->startOfDay(),
        ];
    }
}
