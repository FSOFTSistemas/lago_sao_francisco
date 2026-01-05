<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'passaporte',
        'cpf',
        'rg',
        'orgao_expedidor',
        'nascimento',
        'sexo',
        'profissao',
        'observacao',
        'status',
        'endereco_id',
        'avatar'
    ];

    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function historicoEstadias()
    {
        return $this->reservas()->whereNotNull('data_saida')->orderByDesc('data_entrada');
    }
}
