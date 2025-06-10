<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco',
        'inscricao_estadual',
        'contador_id',
        'responsavel_tecnico_id'
    ];

    public function contador(): BelongsTo
    {
        return $this->belongsTo(EmpresaContador::class, 'contador_id');
    }

    public function responsavelTecnico(): BelongsTo
    {
        return $this->belongsTo(EmpresaRT::class, 'responsavel_tecnico_id');
    }

}
