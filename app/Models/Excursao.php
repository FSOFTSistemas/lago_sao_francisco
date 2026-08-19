<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excursao extends Model
{
    use HasFactory;

    public const STATUS_AGENDADO = 'AGENDADO';

    public const STATUS_REALIZADO = 'REALIZADO';

    public const STATUS_CANCELADO = 'CANCELADO';

    public const STATUS = [
        self::STATUS_AGENDADO,
        self::STATUS_REALIZADO,
        self::STATUS_CANCELADO,
    ];

    protected $table = 'excursoes';

    protected $fillable = [
        'data',
        'qtd_pessoas',
        'valor',
        'status',
        'responsavel',
        'telefone_responsavel',
        'descricao',
    ];

    protected $casts = [
        'data' => 'date',
        'qtd_pessoas' => 'integer',
        'valor' => 'decimal:2',
    ];
}
