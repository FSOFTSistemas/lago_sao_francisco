<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayUseSouvenir extends Model
{
    use HasFactory;
    protected $table = 'day_use_souvenir';

    protected $fillable = [
        'dayuse_id',
        'souvenir_id',
        'quantidade',
        'valor_unitario'
    ];

    public function dayUse()
    {
        return $this->belongsTo(DayUse::class, 'dayuse_id');
    }

    public function souvenir()
    {
        return $this->belongsTo(Souvenir::class, 'souvenir_id');
    }
}
