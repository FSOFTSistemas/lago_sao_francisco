<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemEntrada extends Model
{
    use HasFactory;

    protected $table = 'itens_entradas';

    protected $fillable = [
        'entrada_id',
        'empresa_id',
        'produto_id',
        'almoxarifado_item_id',
        'destino',
        'numero_item',
        'codigo_fornecedor',
        'codigo_barras',
        'descricao',
        'ncm',
        'cest',
        'cfop',
        'unidade',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'valor_desconto',
        'valor_frete',
        'valor_seguro',
        'valor_outras_despesas',
        'cst_icms',
        'csosn',
        'base_icms',
        'aliquota_icms',
        'valor_icms',
        'base_icms_st',
        'aliquota_icms_st',
        'valor_icms_st',
        'cst_pis',
        'valor_pis',
        'cst_cofins',
        'valor_cofins',
        'cst_ipi',
        'valor_ipi',
        'cClassTrib',
        'pIBS',
        'pCBS',
        'cst_ibs_cbs',
    ];

    protected $casts = [
        'numero_item'           => 'integer',
        'quantidade'            => 'decimal:4',
        'valor_unitario'        => 'decimal:6',
        'valor_total'           => 'decimal:2',
        'valor_desconto'        => 'decimal:2',
        'valor_frete'           => 'decimal:2',
        'valor_seguro'          => 'decimal:2',
        'valor_outras_despesas' => 'decimal:2',
        'base_icms'             => 'decimal:2',
        'aliquota_icms'         => 'decimal:4',
        'valor_icms'            => 'decimal:2',
        'base_icms_st'          => 'decimal:2',
        'aliquota_icms_st'      => 'decimal:4',
        'valor_icms_st'         => 'decimal:2',
        'valor_pis'             => 'decimal:2',
        'valor_cofins'          => 'decimal:2',
        'valor_ipi'             => 'decimal:2',
        'pIBS'                  => 'decimal:4',
        'pCBS'                  => 'decimal:4',
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'entrada_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function almoxarifadoItem(): BelongsTo
    {
        return $this->belongsTo(AlmoxarifadoItem::class, 'almoxarifado_item_id');
    }

    public function scopeDaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function getValorUnitarioFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float) ($this->valor_unitario ?? 0), 2, ',', '.');
    }

    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float) ($this->valor_total ?? 0), 2, ',', '.');
    }

    public function getQuantidadeFormatadaAttribute(): string
    {
        $valor = (float) $this->quantidade;
        return floor($valor) == $valor ? number_format($valor, 0, ',', '.') : number_format($valor, 2, ',', '.');
    }

    public function getDestinoFormatadoAttribute(): string
    {
        return match ($this->destino) {
            'almoxarifado' => 'Almoxarifado (Consumo)',
            default        => 'Produto (Venda)',
        };
    }
}
