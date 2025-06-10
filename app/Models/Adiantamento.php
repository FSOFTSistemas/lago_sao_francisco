<?php

namespace App\Models;

use App\Scopes\EmpresaScope;
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


    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
    protected static function booted()
    {
        static::addGlobalScope(new EmpresaScope);
    }
}

