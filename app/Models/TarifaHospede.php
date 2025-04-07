<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifaHospede extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'n_adulto',
        'n_crianca',
        'desconto',
        'tipo' //desconto em porcentagem ou valor
    ];
}
