<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excursao extends Model
{
    use HasFactory;

    protected $table = 'excursoes';

    protected $fillable = [
        'data',
        'qtd_pessoas',
        'valor',
    ];

    protected $casts = [
        'data' => 'date',
        'qtd_pessoas' => 'integer',
        'valor' => 'decimal:2',
    ];
}
