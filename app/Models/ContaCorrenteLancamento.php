<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCorrenteLancamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'data',
        'tipo',
        'status',
        'banco_id',
        'empresa_id'
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
