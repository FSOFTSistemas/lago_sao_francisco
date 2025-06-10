<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecoesCardapio extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome_secao_cardapio',
        'opcao_conteudo_principal_refeicao',
        'ordem_exibicao',
        'cardapio_id'
    ];

    public function cardapio(){
        return $this->belongsTo(Cardapio::class, 'cardapio_id');
    }
    public function categorias()
{
    return $this->hasMany(CategoriasDeItensCardapio::class, 'sessao_cardapio_id');
}

}
