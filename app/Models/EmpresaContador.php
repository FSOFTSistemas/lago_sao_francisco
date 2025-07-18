<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpresaContador extends Model
{
    use HasFactory;

    protected $fillable = [
        'cnpj',
        'nome',
        'crc',
        'email',
        'telefone',
    ];
}
