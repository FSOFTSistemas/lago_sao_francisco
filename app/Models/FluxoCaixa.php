<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FluxoCaixa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'data',
        'tipo',
        'caixa_id',
        'usuario_id',
        'empresa_id',
        'movimento',
        'valor_total',
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function caixa(){
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
