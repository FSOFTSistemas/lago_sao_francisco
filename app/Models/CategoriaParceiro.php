<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaParceiro extends Model
{
    use HasFactory;
    protected $fillable = [
        'descricao'
    ];

    public function parceiros()
    {
       return $this->hasMany(Parceiro::class, 'categoria_id');
    }
}
