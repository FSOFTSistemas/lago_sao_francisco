<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_acao',
        'descricao',
        'data_hora',
        'usuario_id',
    ];

    public function usuario(){
        return $this->belongsTo(User::class, 'usuario_id' );
    }
}
