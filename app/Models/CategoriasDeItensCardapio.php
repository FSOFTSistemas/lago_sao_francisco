<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriasDeItensCardapio extends Model
{
    use HasFactory;
    protected $fillable = [
        'sessao_cardapio_id',
        'refeicao_principal_id',
        'nome_categoria_item',
        'numero_escolhas_permitidas',
        'eh_grupo_escolha_exclusiva',
        'ordem_exibicao',   
    ];

    public function secaoCardapio(){
        return $this->belongsTo(SecoesCardapio::class, 'sessao_cardapio_id');
    }

    public function refeicaoPrincipal(){
        return $this->belongsTo(RefeicaoPrincipal::class, 'refeicao_principal_id');
    }

    public function itens()
{
    return $this->hasMany(DisponibilidadeItemCategoria::class, 'CategoriaItemID')
        ->with('itemID');
}

}
