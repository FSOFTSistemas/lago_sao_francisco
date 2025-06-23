<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdicionalAluguel extends Model
{
    use HasFactory;

    protected $table = 'adicionais_aluguel';

    protected $fillable = [
        'aluguel_id',
        'adicional_id',
        'quantidade',
        'valor_total',
        'observacao',
    ];

    public function adicional()
    {
        return $this->belongsTo(Adicional::class);
    }
}
