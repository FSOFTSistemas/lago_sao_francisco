<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaRT extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'cnpj',
        'telefone',
        'email',
    ];
}
