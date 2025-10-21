<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'quarto_id',
        'hospede_id',
        'data_checkin',
        'data_checkout',
        'valor_diaria',
        'valor_total',
        'situacao',
        'n_adultos',
        'n_criancas',
        'observacoes',
        'placa_veiculo'
    ];

    public function quarto()
    {
        return $this->belongsTo(Quarto::class, 'quarto_id');
    }

    public function hospede()
    {
        return $this->belongsTo(Hospede::class, 'hospede_id');
    }

    public function transacoes()
    {
        return $this->hasMany(Transacao::class);
    }
}
