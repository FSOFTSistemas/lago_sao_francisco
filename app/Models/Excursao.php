<?php

namespace App\Models;

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
        'comissao',
        'valor_almoco',
        'qtd_almoco',
        'total_almoco',
        'subtotal',
        'acrescimo',
        'desconto',
        'total',
        'status',
        'responsavel',
        'telefone_responsavel',
        'descricao',
    ];

    protected $casts = [
        'data' => 'date',
        'qtd_pessoas' => 'integer',
        'valor_pessoa' => 'decimal:2',
        'comissao' => 'decimal:2',
        'valor_almoco' => 'decimal:2',
        'qtd_almoco' => 'integer',
        'total_almoco' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'acrescimo' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Excursao $excursao) {
            $excursao->subtotal ??= $excursao->valor_pessoa;
            $excursao->total ??= $excursao->valor_pessoa;
        });
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(RecebimentoExcursao::class);
    }
}
