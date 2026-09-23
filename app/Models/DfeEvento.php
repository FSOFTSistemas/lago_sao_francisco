<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DfeEvento extends Model
{
    use HasFactory;

    protected $table = 'dfe_eventos';

    protected $fillable = [
        'empresa_id',
        'dfe_documento_id',
        'chave',
        'nsu',
        'tipo_evento',
        'nome_evento',
        'sequencia_evento',
        'protocolo',
        'data_evento',
        'cstat',
        'motivo',
        'justificativa',
        'detalhes',
        'xml',
        'user_id',
    ];

    protected $casts = [
        'data_evento'      => 'datetime',
        'sequencia_evento' => 'integer',
        'detalhes'         => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DfeDocumento::class, 'dfe_documento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNomeEventoFormatadoAttribute(): string
    {
        if (!empty($this->nome_evento)) {
            return $this->nome_evento;
        }

        return match ($this->tipo_evento) {
            '210210' => 'Ciência da Emissão',
            '210200' => 'Confirmação da Operação',
            '210220' => 'Desconhecimento da Operação',
            '210240' => 'Operação Não Realizada',
            '110111' => 'Cancelamento de NF-e',
            '110110' => 'Carta de Correção Eletrônica (CC-e)',
            '110130' => 'Comprovante de Entrega',
            '110131' => 'Cancelamento de Comprovante de Entrega',
            '110150' => 'Ator Interessado na NF-e',
            default  => 'Evento ' . $this->tipo_evento,
        };
    }

    public function getBadgeClasseAttribute(): string
    {
        return match ($this->tipo_evento) {
            '210200' => 'badge-success',
            '210210' => 'badge-info',
            '210220' => 'badge-warning',
            '210240' => 'badge-danger',
            '110111' => 'badge-danger',
            '110110' => 'badge-primary',
            default  => 'badge-secondary',
        };
    }
}
