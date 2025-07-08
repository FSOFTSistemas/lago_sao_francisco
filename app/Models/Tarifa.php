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
        'categoria_id',
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
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
