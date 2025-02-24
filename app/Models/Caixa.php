<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'valor_inicial',
        'valor_final',
        'data_abertura',
        'data_fechamento',
        'status',
        'usuario_abertura_id',
        'usuario_fechamento_id',
        'observacoes',
        'empresa_id',
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
