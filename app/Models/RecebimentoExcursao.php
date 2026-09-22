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
        'fluxo_caixa_id',
        'fluxo_cancelamento_id',
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

    public function fluxoCaixa(): BelongsTo
    {
        return $this->belongsTo(FluxoCaixa::class, 'fluxo_caixa_id');
    }

    public function fluxoCancelamento(): BelongsTo
    {
        return $this->belongsTo(FluxoCaixa::class, 'fluxo_cancelamento_id');
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

        return null;
    }
}
