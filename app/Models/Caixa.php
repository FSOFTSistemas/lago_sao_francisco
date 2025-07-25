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
        'usuario_id',
        'usuario_abertura_id',
        'usuario_fechamento_id',
        'observacoes',
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
    public function usuarioAbertura()
    {
        return $this->belongsTo(User::class, 'usuario_abertura_id');
    }
    public function usuarioFechamento()
    {
        return $this->belongsTo(User::class, 'usuario_fechamento_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
