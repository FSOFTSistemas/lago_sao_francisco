<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaPet extends Model
{
    use HasFactory;

    protected $fillable = [
        'reserva_id',
        'tamanho',      // pequeno, medio, grande
        'quantidade',
        'valor_unitario' // valor da diária do pet
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
    
    // Calcula o total deste item (Qtd Pets * Valor Unitario * Dias da Reserva)
    // Note que precisaremos saber os dias da reserva para o calculo final no controller
}