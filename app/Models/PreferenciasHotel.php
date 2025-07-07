<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenciasHotel extends Model
{
    use HasFactory;
    protected $fillable = [
        'checkin',
        'checkout',
        'limpeza_quarto',
        'valor_diaria'
    ];
}
