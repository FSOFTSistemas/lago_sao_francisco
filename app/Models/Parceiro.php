<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parceiro extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'valor',
        'categoria_id'
    ];

    public function categoria()
    {
       return $this->belongsTo(CategoriaParceiro::class, 'categoria_id');
    }
}
