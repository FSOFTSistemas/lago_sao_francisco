<?php

namespace App\Services\Financeiro;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FinancialSummaryCalculator
{
    public function calculate(
        Collection $receivables,
        Collection $payables,
        Collection $cashFlow,
        CarbonInterface $today,
    ): array {
        $receivables = $receivables->map(function (array $item) use ($today) {
            $remaining = $this->remaining($item['value'], $item['received']);
            $overdue = $remaining > 0
                && ($item['status'] === 'atrasado' || $item['due_date']->lt($today));

            return [...$item, 'remaining' => $remaining, 'overdue' => $overdue];
        });

        $payables = $payables->map(function (array $item) use ($today) {
            $remaining = $this->remaining($item['value'], $item['paid']);
            $paid = in_array($item['status'], ['pago', 'finalizado'], true) || $remaining === 0.0;

            return [
                ...$item,
                'remaining' => $remaining,
                'paid_status' => $paid,
                'overdue' => ! $paid && $item['due_date']->lt($today),
            ];
        });

        $receivableReceived = $this->sum($receivables, 'received');
        $receivableOverdue = $this->sum($receivables->where('overdue', true), 'remaining');
        $receivablePending = $this->sum(
            $receivables->where('overdue', false)->where('remaining', '>', 0),
            'remaining',
        );

        $payablePaid = $this->sum($payables, 'paid');
        $payableOverdue = $this->sum($payables->where('overdue', true), 'remaining');
        $payablePending = $this->sum(
            $payables->where('overdue', false)->where('remaining', '>', 0),
            'remaining',
        );

        $cashEntries = $this->sum($cashFlow->where('type', 'entrada'), 'value');
        $cashExits = $this->sum($cashFlow->where('type', 'saida'), 'value');
        $cashCancellations = $this->sum($cashFlow->where('type', 'cancelamento'), 'value');

        return [
            'receivables' => [
                'received' => $receivableReceived,
                'pending' => $receivablePending,
                'overdue' => $receivableOverdue,
                'open' => round($receivablePending + $receivableOverdue, 2),
                'pending_count' => $receivables->where('overdue', false)->where('remaining', '>', 0)->count(),
                'overdue_count' => $receivables->where('overdue', true)->count(),
            ],
            'payables' => [
                'paid' => $payablePaid,
                'pending' => $payablePending,
                'overdue' => $payableOverdue,
                'open' => round($payablePending + $payableOverdue, 2),
                'pending_count' => $payables->where('overdue', false)->where('remaining', '>', 0)->count(),
                'overdue_count' => $payables->where('overdue', true)->count(),
            ],
            'cash_flow' => [
                'entries' => $cashEntries,
                'exits' => $cashExits,
                'cancellations' => $cashCancellations,
                'net' => round($cashEntries - $cashExits - $cashCancellations, 2),
            ],
            'projection' => [
                'net_open' => round(
                    ($receivablePending + $receivableOverdue)
                    - ($payablePending + $payableOverdue),
                    2,
                ),
            ],
        ];
    }

    private function remaining(float $total, float $settled): float
    {
        return round(max(0, $total - $settled), 2);
    }

    private function sum(Collection $items, string $field): float
    {
        return round((float) $items->sum($field), 2);
    }
}
