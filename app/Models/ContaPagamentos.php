<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagamentos extends Model
{
    use HasFactory;
    protected $fillable = [
        'conta_id',
        'cliente_id',
        'data_recebimento',
        'valor_recebido',
        'usuario_id',
    ];

    public function conta()
    {
        return $this->belongsTo(ContasAReceber::class, 'conta_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
