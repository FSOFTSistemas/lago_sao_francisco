<?php

namespace Tests\Unit;

use App\Services\Financeiro\FinancialSummaryCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class FinancialSummaryCalculatorTest extends TestCase
{
    public function test_calcula_resumo_sem_incluir_dados_de_hotel(): void
    {
        $result = (new FinancialSummaryCalculator)->calculate(
            new Collection([
                $this->receivable(1000, 250, 'pendente', '2026-08-25'),
                $this->receivable(500, 100, 'atrasado', '2026-08-10'),
                $this->receivable(300, 300, 'recebido', '2026-08-05'),
            ]),
            new Collection([
                $this->payable(600, 100, 'pendente', '2026-08-28'),
                $this->payable(200, 50, 'pendente', '2026-08-01'),
                $this->payable(90, 90, 'pago', '2026-08-03'),
            ]),
            new Collection([
                ['type' => 'entrada', 'value' => 800.0],
                ['type' => 'saida', 'value' => 275.0],
                ['type' => 'cancelamento', 'value' => 25.0],
                ['type' => 'abertura', 'value' => 100.0],
            ]),
            CarbonImmutable::parse('2026-08-22'),
        );

        $this->assertSame([
            'received' => 650.0,
            'pending' => 750.0,
            'overdue' => 400.0,
            'open' => 1150.0,
            'pending_count' => 1,
            'overdue_count' => 1,
        ], $result['receivables']);
        $this->assertSame([
            'paid' => 240.0,
            'pending' => 500.0,
            'overdue' => 150.0,
            'open' => 650.0,
            'pending_count' => 1,
            'overdue_count' => 1,
        ], $result['payables']);
        $this->assertSame([
            'entries' => 800.0,
            'exits' => 275.0,
            'cancellations' => 25.0,
            'net' => 500.0,
        ], $result['cash_flow']);
        $this->assertSame(['net_open' => 500.0], $result['projection']);
    }

    public function test_status_pendente_vencido_e_tratado_como_atrasado_mesmo_antes_do_cron(): void
    {
        $result = (new FinancialSummaryCalculator)->calculate(
            new Collection([$this->receivable(100, 0, 'pendente', '2026-08-21')]),
            new Collection([$this->payable(80, 0, 'pendente', '2026-08-21')]),
            new Collection,
            CarbonImmutable::parse('2026-08-22'),
        );

        $this->assertSame(100.0, $result['receivables']['overdue']);
        $this->assertSame(80.0, $result['payables']['overdue']);
    }

    private function receivable(float $value, float $received, string $status, string $dueDate): array
    {
        return [
            'value' => $value,
            'received' => $received,
            'status' => $status,
            'due_date' => CarbonImmutable::parse($dueDate),
        ];
    }

    private function payable(float $value, float $paid, string $status, string $dueDate): array
    {
        return [
            'value' => $value,
            'paid' => $paid,
            'status' => $status,
            'due_date' => CarbonImmutable::parse($dueDate),
        ];
    }
}
