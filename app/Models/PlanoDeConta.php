<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoDeConta extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'tipo',
        'plano_de_conta_pai',
        'empresa_id',
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
