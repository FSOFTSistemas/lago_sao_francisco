<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adiantamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'data',
        'status',
        'funcionario_id',
        'empresa_id',
    ];


    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}

