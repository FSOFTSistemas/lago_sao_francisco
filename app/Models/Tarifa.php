<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nome',
        'ativo',
        'observacoes',
        'categoria',
        'seg',
        'ter',
        'qua',
        'qui',
        'sex',
        'sab',
        'dom',
        'tarifa_hospede_id',
    ];
    public function tarifaHospede()
    {
        return $this->belongsTo(TarifaHospede::class, 'tarifa_hospede_id');
    }
}
