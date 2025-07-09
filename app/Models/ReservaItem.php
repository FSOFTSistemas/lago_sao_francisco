<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'reserva_id',
        'produto_id',
        'quantidade'
    ];

     public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    // Accessors
    public function getTotalAttribute()
    {
        return $this->quantidade * $this->produto->preco_venda;
    }

    public function getTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->total, 2, ',', '.');
    }
}
