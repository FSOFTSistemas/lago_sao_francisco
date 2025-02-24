<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaCartao extends Model
{
    use HasFactory;
    protected $fillable = [
        'conta_id',
        'banco_id',
        'cliente_id',
        'venda_id',
        'valor',
        'data_baixa',
        'status',
        'taxa',
        'parcela',
        'empresa_id',
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
