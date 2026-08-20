<?php

namespace App\Models;

use App\Services\ExcursaoFinanceiroService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
            $calculos = (new ExcursaoFinanceiroService)->calcular([
                'qtd_pessoas' => $excursao->qtd_pessoas,
                'valor_pessoa' => $excursao->valor_pessoa,
                'percentual_comissao' => $excursao->percentual_comissao,
                'valor_almoco' => $excursao->valor_almoco,
                'qtd_almoco' => $excursao->qtd_almoco,
                'acrescimo' => $excursao->acrescimo,
                'desconto' => $excursao->desconto,
            ]);
            $excursao->total_almoco = $calculos['total_almoco'];
            $excursao->subtotal = $calculos['subtotal'];
            $excursao->total = $calculos['total'];
        });
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(RecebimentoExcursao::class);
    }

    public function almoco(): HasOne
    {
        return $this->hasOne(ExcursaoAlmoco::class);
    }

    protected function valorPessoas(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['valor_pessoas']);
    }

    protected function valorComissao(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['valor_comissao']);
    }

    protected function valorPago(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['valor_pago']);
    }

    protected function valorRestante(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['valor_restante']);
    }

    protected function receitaLiquida(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['receita_liquida']);
    }

    protected function percentualPago(): Attribute
    {
        return Attribute::get(function () {
            $calculos = $this->calculosFinanceiros();

            return round(min(
                ($calculos['valor_pago'] / $calculos['total']) * 100,
                100,
            ), 2);
        });
    }

    protected function pagamentoMinimoAtingido(): Attribute
    {
        return Attribute::get(function () {
            $calculos = $this->calculosFinanceiros();

            return $calculos['valor_pago'] + 0.01 >= $calculos['total'] * 0.5;
        });
    }

    protected function quitada(): Attribute
    {
        return Attribute::get(fn () => $this->calculosFinanceiros()['quitada']);
    }

    /** @return array<string, float|bool> */
    private function calculosFinanceiros(): array
    {
        return (new ExcursaoFinanceiroService)->calcularParaExcursao($this);
    }
}
