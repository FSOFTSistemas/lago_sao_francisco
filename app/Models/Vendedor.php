<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendedor extends Model
{
    use HasFactory;

    protected $table = 'vendedors'; // Nome da tabela no banco de dados

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'endereco',
    ];

    // Exemplo de relacionamento com vendas (se existir)
    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }
}
