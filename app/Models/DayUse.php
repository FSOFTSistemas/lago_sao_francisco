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
        'total'
    ];

    public function itens()
    {
        return $this->hasMany(MovDayUse::class, 'dayuse_id')->with('item');
    }

    public function formaPag()
    {
        return $this->hasMany(DayUsePag::class, 'dayuse_id')->with('forma');
    }

    public function cliente()
    {
        $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
}
