<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacoteEventoAluguel extends Model
{
    use HasFactory;

    protected $table = 'pacote_evento_aluguel';

    protected $fillable = [
        'aluguel_id',
        'pacote_evento_id',
        'quantidade',
        'valor_total',
        'observacao',
    ];

    public function pacoteEvento()
    {
        return $this->belongsTo(PacoteEvento::class);
    }

    public function aluguel()
    {
        return $this->belongsTo(Aluguel::class);
    }
}
