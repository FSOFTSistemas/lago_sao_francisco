<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovDayUse extends Model
{
    use HasFactory;
    protected $fillable = [
        'dayuse_id',
        'item_dayuse_id',
        'quantidade',
        'valor_unitario'
    ];

    public function item()
    {
        return $this->belongsTo(ItensDayUse::class, 'item_dayuse_id', 'id');
    }

    public function dayuse()
    {
        return $this->belongsTo(DayUse::class, 'dayuse_id', 'id');
    }
}
