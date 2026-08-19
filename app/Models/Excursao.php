<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Excursao extends Model
{
    use HasFactory;

    public const STATUS_AGENDADO = 'AGENDADO';

    public const STATUS_REALIZADO = 'REALIZADO';

    public const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';

    public const STATUS_CANCELADO = 'CANCELADO';

    public const STATUS = [
        self::STATUS_AGENDADO,
        self::STATUS_EM_ANDAMENTO,
        self::STATUS_REALIZADO,
        self::STATUS_CANCELADO,
    ];

    protected $table = 'excursoes';

    protected $fillable = [
        'data',
        'qtd_pessoas',
        'valor_pessoa',
        'percentual_comissao',
        'valor_almoco',
        'qtd_almoco',
        'total_almoco',
        'subtotal',
        'acrescimo',
        'desconto',
        'total',
        'status',
        'iniciada_em',
        'finalizada_em',
        'cancelada_em',
        'motivo_cancelamento',
        'responsavel',
        'telefone_responsavel',
        'descricao',
    ];

    protected $casts = [
        'data' => 'date',
        'qtd_pessoas' => 'integer',
        'valor_pessoa' => 'decimal:2',
        'percentual_comissao' => 'decimal:2',
        'valor_almoco' => 'decimal:2',
        'qtd_almoco' => 'integer',
        'total_almoco' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'acrescimo' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2',
        'iniciada_em' => 'datetime',
        'finalizada_em' => 'datetime',
        'cancelada_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Excursao $excursao) {
            $excursao->percentual_comissao ??= 10;
            $excursao->total_almoco ??= (float) $excursao->valor_almoco * (int) $excursao->qtd_almoco;
            $excursao->subtotal ??= ((float) $excursao->valor_pessoa * (int) $excursao->qtd_pessoas)
                + (float) $excursao->total_almoco;
            $excursao->total ??= (float) $excursao->subtotal
                + (float) $excursao->acrescimo
                - (float) $excursao->desconto;
        });
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(RecebimentoExcursao::class);
    }

    protected function valorPessoas(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->valor_pessoa * (int) $this->qtd_pessoas,
            2,
        ));
    }

    protected function valorComissao(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->valor_pessoas * ((float) $this->percentual_comissao / 100),
            2,
        ));
    }

    protected function valorPago(): Attribute
    {
        return Attribute::get(function () {
            $valor = $this->relationLoaded('recebimentos')
                ? $this->recebimentos->sum('valor')
                : $this->recebimentos()->sum('valor');

            return round((float) $valor, 2);
        });
    }

    protected function valorRestante(): Attribute
    {
        return Attribute::get(fn () => round(max(
            (float) $this->total - (float) $this->valor_pago,
            0,
        ), 2));
    }

    protected function receitaLiquida(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->total - (float) $this->valor_comissao,
            2,
        ));
    }

    protected function percentualPago(): Attribute
    {
        return Attribute::get(function () {
            if ((float) $this->total <= 0) {
                return 0.0;
            }

            return round(min(
                ((float) $this->valor_pago / (float) $this->total) * 100,
                100,
            ), 2);
        });
    }

    protected function pagamentoMinimoAtingido(): Attribute
    {
        return Attribute::get(fn () => (float) $this->valor_pago + 0.01
            >= (float) $this->total * 0.5);
    }

    protected function quitada(): Attribute
    {
        return Attribute::get(fn () => (float) $this->valor_restante <= 0.01);
    }
}
