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
        'movimento_id',
        'valor_total',
        'plano_de_conta_id'
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function caixa(){
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function movimento(){
        return $this->belongsTo(Movimento::class, 'movimento_id');
    }
    public function planoDeConta(){
        return $this->belongsTo(PlanoDeConta::class, 'plano_de_conta_id');
    }
}
