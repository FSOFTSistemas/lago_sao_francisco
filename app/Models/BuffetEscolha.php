<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuffetEscolha extends Model
{
    use HasFactory;

    protected $table = 'buffet_escolhas';

    protected $fillable = [
        'aluguel_id',
        'tipo',
        'categoria_id',
        'item_id',
        'opcao_refeicao_id',
    ];

    // Relacionamentos
    public function aluguel()
    {
        return $this->belongsTo(Aluguel::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriasDeItensCardapio::class);
    }

    public function item()
    {
        return $this->belongsTo(ItensDoCardapio::class);
    }

    public function opcaoRefeicao()
    {
        return $this->belongsTo(RefeicaoPrincipal::class);
    }
}

