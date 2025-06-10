<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'nome_razao_social',
        'apelido_nome_fantasia',
        'telefone',
        'whatsapp',
        'data_nascimento',
        'endereco_id',
        'cpf_cnpj',
        'rg_ie',
        'empresa_id',
        'tipo'
    ];
    public function daEmpresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }
}
