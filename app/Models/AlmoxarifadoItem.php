<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlmoxarifadoItem extends Model
{
    use HasFactory;

    protected $table = 'almoxarifado_itens';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'nome',
        'unidade_medida',
        'estoque_atual',
        'estoque_minimo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'estoque_atual' => 'decimal:2',
        'estoque_minimo' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(AlmoxarifadoCategoria::class, 'categoria_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(AlmoxarifadoMovimentacao::class, 'item_id');
    }

    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeEstoqueBaixo($query)
    {
        return $query->whereColumn('estoque_atual', '<=', 'estoque_minimo')
                     ->where('estoque_minimo', '>', 0);
    }

    public function getIsEstoqueBaixoAttribute(): bool
    {
        return (float) $this->estoque_minimo > 0 && (float) $this->estoque_atual <= (float) $this->estoque_minimo;
    }

    public function getEstoqueFormatadoAttribute(): string
    {
        $valor = (float) $this->estoque_atual;
        return floor($valor) == $valor ? number_format($valor, 0, ',', '.') : number_format($valor, 2, ',', '.');
    }
}
