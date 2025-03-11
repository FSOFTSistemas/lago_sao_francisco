<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCorrente extends Model
{
    use HasFactory;
    protected $table = 'contas_correntes';
    protected $fillable = [
        'id',
        'descricao',
        'numero_conta',
        'banco_id',
        'titular',
        'saldo'
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function banco()
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }
}
