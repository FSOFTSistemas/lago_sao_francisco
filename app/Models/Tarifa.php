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
        // --- NOVOS CAMPOS ADICIONADOS ---
        'alta_temporada',
        'data_inicio',
        'data_fim',
        // --------------------------------
        'seg',
        'ter',
        'qua',
        'qui',
        'sex',
        'sab',
        'dom',
        'tarifa_hospede_id',
        'padrao_adultos',
        'padrao_criancas',
        'adicional_adulto',
        'adicional_crianca'
    ];

    // Opcional: Garante a tipagem correta ao buscar do banco
    protected $casts = [
        'alta_temporada' => 'boolean',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
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