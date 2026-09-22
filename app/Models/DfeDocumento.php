<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DfeDocumento extends Model
{
    use HasFactory;

    protected $table = 'dfe_documentos';

    protected $fillable = [
        'empresa_id',
        'nsu',
        'chave',
        'schema',
        'tipo_documento',
        'cnpj_emitente',
        'nome_emitente',
        'ie_emitente',
        'valor_total',
        'data_emissao',
        'tipo_nfe',
        'situacao_nfe',
        'situacao_manifestacao',
        'data_manifestacao',
        'protocolo_manifestacao',
        'mensagem_manifestacao',
        'xml',
        'importado_entrada',
        'entrada_id',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'data_emissao' => 'datetime',
        'data_manifestacao' => 'datetime',
        'importado_entrada' => 'boolean',
        'tipo_nfe' => 'integer',
        'situacao_nfe' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'entrada_id');
    }

    public function scopeDaEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeComXml(Builder $query): Builder
    {
        return $query->whereNotNull('xml')->where('xml', '!=', '');
    }

    public function scopeNaoImportados(Builder $query): Builder
    {
        return $query->where('importado_entrada', false);
    }

    public function scopePendentesManifestacao(Builder $query): Builder
    {
        return $query->where('situacao_manifestacao', 'sem_manifestacao');
    }

    public function getSituacaoManifestacaoFormatadaAttribute(): string
    {
        return match ($this->situacao_manifestacao) {
            'ciencia'          => 'Ciência da Emissão',
            'confirmada'       => 'Operação Confirmada',
            'desconhecida'     => 'Desconhecimento',
            'nao_realizada'    => 'Operação Não Realizada',
            default            => 'Sem Manifestação',
        };
    }

    public function getBadgeManifestacaoAttribute(): string
    {
        return match ($this->situacao_manifestacao) {
            'ciencia'          => 'badge-info',
            'confirmada'       => 'badge-success',
            'desconhecida'     => 'badge-warning',
            'nao_realizada'    => 'badge-danger',
            default            => 'badge-secondary',
        };
    }

    public function getSituacaoNfeFormatadaAttribute(): string
    {
        return match ((int) $this->situacao_nfe) {
            1 => 'Autorizada',
            2 => 'Cancelada',
            3 => 'Denegada',
            default => 'Autorizada',
        };
    }

    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float) ($this->valor_total ?? 0), 2, ',', '.');
    }

    public function temXmlCompleto(): bool
    {
        return !empty($this->xml) && in_array($this->schema, ['procNFe', 'nfeProc']);
    }
}
