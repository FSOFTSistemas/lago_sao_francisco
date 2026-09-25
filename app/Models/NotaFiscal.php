<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class NotaFiscal extends Model
{
    use HasFactory;

    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_GERADA = 'gerada';

    public const STATUS_ASSINADA = 'assinada';

    public const STATUS_AUTORIZADA = 'autorizada';

    public const STATUS_REJEITADA = 'rejeitada';

    public const STATUS_DENEGADA = 'denegada';

    public const STATUS_CANCELADA = 'cancelada';

    protected $fillable = [
        'id',
        'cliente_id',
        'ncm_id',
        'cfop_id',
        'usuario_id',
        'data',
        'empresa_id',
        'chave',
        'status',
        'cstat',
        'protocolo',
        'motivo_status',
        'data_autorizacao',
        'numero',
        'serie',
        'observacoes',
        'info_complementares',
        'peso_liquido',
        'peso_bruto',
        'pt_frete',
        'pt_transporte',
        'pt_nota',
        'nfe_referenciavel',
        'total_produtos',
        'total_nota',
        'total_notas',
        'total_desconto',
        'outras_despesas',
        'base_ICMS',
        'vICMS',
        'base_ST',
        'v_ST',
        'vST',
    ];

    protected $casts = [
        'data' => 'date',
        'data_autorizacao' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cfop()
    {
        return $this->belongsTo(CFOP::class, 'cfop_id');
    }

    public function ncm()
    {
        return $this->belongsTo(NCM::class, 'ncm_id');
    }

    public function itens()
    {
        return $this->hasMany(NotaFiscalItem::class, 'nota_fiscal_id');
    }

    public function isAssinada(): bool
    {
        return $this->status === self::STATUS_ASSINADA
            || $this->isAutorizada()
            || (! empty($this->chave) && File::exists(storage_path("app/nfe/assinadas/{$this->chave}.xml")));
    }

    public function isAutorizada(): bool
    {
        return $this->status === self::STATUS_AUTORIZADA
            || (! empty($this->chave) && File::exists(storage_path("app/nfe/autorizadas/{$this->chave}.xml")));
    }

    public function isGerada(): bool
    {
        return in_array($this->status, [self::STATUS_GERADA, self::STATUS_ASSINADA, self::STATUS_AUTORIZADA])
            || (! empty($this->chave) && (
                File::exists(storage_path("app/nfe/geradas/{$this->chave}.xml"))
                || $this->isAssinada()
                || $this->isAutorizada()
            ));
    }

    public function isRejeitada(): bool
    {
        return $this->status === self::STATUS_REJEITADA;
    }

    public function isDenegada(): bool
    {
        return $this->status === self::STATUS_DENEGADA;
    }

    public function isCancelada(): bool
    {
        return $this->status === self::STATUS_CANCELADA;
    }

    public function getStatusFormatadoAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AUTORIZADA => 'Autorizada',
            self::STATUS_ASSINADA => 'Assinada',
            self::STATUS_GERADA => 'XML Gerado',
            self::STATUS_REJEITADA => 'Rejeitada',
            self::STATUS_DENEGADA => 'Uso Denegado',
            self::STATUS_CANCELADA => 'Cancelada',
            default => $this->isAutorizada() ? 'Autorizada' : ($this->isAssinada() ? 'Assinada' : ($this->isGerada() ? 'XML Gerado' : 'Pendente')),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_formatado) {
            'Autorizada' => 'bg-success',
            'Assinada' => 'bg-info',
            'XML Gerado' => 'bg-secondary',
            'Rejeitada' => 'bg-danger',
            'Uso Denegado' => 'bg-dark',
            'Cancelada' => 'bg-warning text-dark',
            default => 'bg-light text-dark',
        };
    }
}
