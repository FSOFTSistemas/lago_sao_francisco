<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motorhome extends Model
{
    use HasFactory;

    protected $fillable = [
        'placa',
        'modelo',
        'comprimento',
        'cor',
        'hospede_id',
        'observacoes',
        'status',
    ];

    public function proprietario()
    {
        return $this->belongsTo(Hospede::class, 'hospede_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}
