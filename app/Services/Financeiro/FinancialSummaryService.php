<?php

namespace App\Services\Financeiro;

use App\Support\CompanyContext;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class FinancialSummaryService
{
    public function __construct(
        private readonly CompanyContext $companyContext,
        private readonly FinancialSummaryRepository $repository,
        private readonly FinancialSummaryCalculator $calculator,
    ) {}

    public function summarize(
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        DateTimeInterface|string|null $today = null,
    ): array {
        $start = CarbonImmutable::parse($start)->startOfDay();
        $end = CarbonImmutable::parse($end)->endOfDay();
        $today = CarbonImmutable::parse($today ?? now())->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException('A data final não pode ser anterior à data inicial.');
        }

        $companyId = $this->companyContext->id();

        $summary = $this->calculator->calculate(
            $this->repository->receivables($companyId, $start, $end),
            $this->repository->payables($companyId, $start, $end),
            $this->repository->cashFlow($companyId, $start, $end),
            $today,
        );

        return [
            'company_id' => $companyId,
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            ...$summary,
        ];
    }
}
