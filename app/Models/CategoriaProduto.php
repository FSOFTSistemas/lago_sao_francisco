<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProduto extends Model
{
    use HasFactory;
    protected $fillable = [
        'descricao',
        'ativo',
    ];

        public function produtos()
    {
        return $this->hasMany(Produto::class, 'categoria_produto_id');
        
    }


}
