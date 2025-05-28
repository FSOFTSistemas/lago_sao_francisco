<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaPreferencia extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'certificado_digital',
        'numero_ultima_nota',
        'serie',
        'cfop_padrao',
        'regime_tributario',
        'empresa_id',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
