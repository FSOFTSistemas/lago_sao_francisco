<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItensDayUse extends Model
{
    use HasFactory;
    protected $fillable = [
        'descricao',
        'valor',
        'passeio'
    ];

    public function movimentacao()
    {
        return $this->hasMany(MovDayUse::class, 'item_dayuse_id');
    }
}
