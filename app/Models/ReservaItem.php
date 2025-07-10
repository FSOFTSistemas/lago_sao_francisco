<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaItem extends Model
{
    use HasFactory;

    protected $table = 'reserva_items';

    protected $fillable = [
        'produto_id',
        'reserva_id',
        'quantidade',
    ];

    protected $casts = [
        'quantidade' => 'integer',
    ];

    // Relacionamentos
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
