<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'saldo_anterior',
        'saldo_posterior',
        'data_movimentacao',
        'user_id',
        'fornecedor',
        'recebido_por',
        'numero_documento',
        'retirado_por',
        'setor',
        'motivo_ajuste',
        'observacao',
    ];

    protected $casts = [
        'quantidade'        => 'decimal:2',
        'saldo_anterior'    => 'decimal:2',
        'saldo_posterior'   => 'decimal:2',
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

    public function scopeDaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeEntradas(Builder $query): Builder
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSaidas(Builder $query): Builder
    {
        return $query->where('tipo', 'saida');
    }

    public function scopeAjustes(Builder $query): Builder
    {
        return $query->where('tipo', 'ajuste');
    }

    public function getTipoFormatadoAttribute(): string
    {
        return match ($this->tipo) {
            'entrada' => 'Entrada',
            'saida'   => 'Saída',
            'ajuste'  => 'Ajuste de Estoque',
            default   => ucfirst($this->tipo),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->tipo) {
            'entrada' => 'badge-success',
            'saida'   => 'badge-danger',
            'ajuste'  => 'badge-info',
            default   => 'badge-secondary',
        };
    }

    public function getQuantidadeFormatadaAttribute(): string
    {
        $valor = (float) $this->quantidade;
        return floor($valor) == $valor ? number_format($valor, 0, ',', '.') : number_format($valor, 2, ',', '.');
    }
}
