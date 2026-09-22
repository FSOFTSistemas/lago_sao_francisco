<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrada extends Model
{
    use HasFactory;

    protected $table = 'entradas';

    protected $fillable = [
        'empresa_id',
        'fornecedor_id',
        'usuario_id',
        'dfe_documento_id',
        'chave',
        'numero_nota',
        'serie',
        'natureza_operacao',
        'data_emissao',
        'data_entrada',
        'tipo_operacao',
        'valor_produtos',
        'valor_frete',
        'valor_seguro',
        'valor_desconto',
        'valor_outras_despesas',
        'valor_icms',
        'valor_icms_st',
        'valor_ipi',
        'valor_total',
        'observacoes',
        'xml',
        'status',
    ];

    protected $casts = [
        'data_emissao'          => 'datetime',
        'data_entrada'          => 'datetime',
        'valor_produtos'        => 'decimal:2',
        'valor_frete'           => 'decimal:2',
        'valor_seguro'          => 'decimal:2',
        'valor_desconto'        => 'decimal:2',
        'valor_outras_despesas' => 'decimal:2',
        'valor_icms'            => 'decimal:2',
        'valor_icms_st'         => 'decimal:2',
        'valor_ipi'             => 'decimal:2',
        'valor_total'           => 'decimal:2',
        'tipo_operacao'         => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function dfeDocumento(): BelongsTo
    {
        return $this->belongsTo(DfeDocumento::class, 'dfe_documento_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemEntrada::class, 'entrada_id');
    }

    public function scopeDaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float) ($this->valor_total ?? 0), 2, ',', '.');
    }

    public function getDataEmissaoFormatadaAttribute(): string
    {
        return $this->data_emissao ? $this->data_emissao->format('d/m/Y H:i') : '-';
    }

    public function getDataEntradaFormatadaAttribute(): string
    {
        return $this->data_entrada ? $this->data_entrada->format('d/m/Y H:i') : '-';
    }

    public function getChaveFormatadaAttribute(): string
    {
        if (strlen($this->chave) === 44) {
            return vsprintf('%s %s %s %s %s %s %s %s %s %s %s', str_split($this->chave, 4));
        }
        return $this->chave;
    }
}
