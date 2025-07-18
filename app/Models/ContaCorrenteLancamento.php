<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCorrenteLancamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'descricao',
        'valor',
        'data',
        'tipo',
        'status',
        'conta_corrente_id',
        'empresa_id'
    ];
    public function Empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function ContaCorrente()
    {
        return $this->belongsTo(ContaCorrente::class, 'conta_corrente_id');
    }
}
