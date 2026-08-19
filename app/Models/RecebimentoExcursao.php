<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecebimentoExcursao extends Model
{
    use HasFactory;

    protected $table = 'recebimento_excursao';

    protected $fillable = [
        'excursao_id',
        'data_recebimento',
        'valor',
        'forma_pagamento_id',
        'comprovante_path',
    ];

    protected $casts = [
        'data_recebimento' => 'date',
        'valor' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new DomainException('Recebimentos de excursão não podem ser editados.');
        });

        static::deleting(function (RecebimentoExcursao $recebimento) {
            $motivo = $recebimento->motivoBloqueioExclusao();

            if ($motivo !== null) {
                throw new DomainException($motivo);
            }
        });
    }

    public function excursao(): BelongsTo
    {
        return $this->belongsTo(Excursao::class);
    }

    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    public function podeSerExcluido(): bool
    {
        return $this->motivoBloqueioExclusao() === null;
    }

    public function motivoBloqueioExclusao(): ?string
    {
        $excursao = $this->relationLoaded('excursao')
            ? $this->excursao
            : $this->excursao()->first();

        if (! $excursao) {
            return 'Não foi possível identificar a excursão deste recebimento.';
        }

        if ($excursao->status !== Excursao::STATUS_AGENDADO) {
            return 'Recebimentos só podem ser excluídos enquanto a excursão estiver agendada.';
        }

        $totalAposExclusao = $this->totalOutrosRecebimentos($excursao);
        $pagamentoMinimo = (float) $excursao->total * 0.5;

        if ($totalAposExclusao + 0.01 < $pagamentoMinimo) {
            return 'O recebimento não pode ser excluído porque o valor pago ficaria abaixo de 50% do total da excursão.';
        }

        return null;
    }

    private function totalOutrosRecebimentos(Excursao $excursao): float
    {
        if ($excursao->relationLoaded('recebimentos')) {
            return round((float) $excursao->recebimentos
                ->where('id', '!=', $this->getKey())
                ->sum('valor'), 2);
        }

        return round((float) $excursao->recebimentos()
            ->whereKeyNot($this->getKey())
            ->sum('valor'), 2);
    }
}
