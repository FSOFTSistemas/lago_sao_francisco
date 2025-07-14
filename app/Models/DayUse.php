<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayUse extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id',
        'data',
        'vendedor_id',
        'total', //subtotal
        'acrescimo',
        'desconto'
    ];

    public function itens()
    {
        return $this->hasMany(MovDayUse::class, 'dayuse_id')->with('item');
    }

    public function formaPag()
    {
        return $this->hasMany(DayUsePag::class, 'dayuse_id')->with('formaPagamento');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Funcionario::class, 'vendedor_id');
    }
    public function souvenirs()
    {
        return $this->belongsToMany(Souvenir::class, 'day_use_souvenir', 'dayuse_id', 'souvenir_id')
            ->withPivot('quantidade')
            ->withTimestamps();
    }
}
