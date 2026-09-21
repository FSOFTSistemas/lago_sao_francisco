<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmoxarifadoMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'almoxarifado_movimentacoes';

    protected $fillable = [
        'empresa_id',
        'item_id',
        'tipo',
        'quantidade',
        'data_movimentacao',
        'user_id',
        'fornecedor',
        'recebido_por',
        'retirado_por',
        'setor',
        'observacao',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'data_movimentacao' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AlmoxarifadoItem::class, 'item_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
