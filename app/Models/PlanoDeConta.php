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
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
    public function planoPai()
    {
        return $this->belongsTo($this, 'plano_de_conta_pai');
    }
    public function filhos()
    {
        return $this->hasMany($this, 'plano_de_conta_pai');
    }
}
