<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogDayuse extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario',
        'supervisor',
        'acao',
        'data_hora',
        'observacao',
    ];
}
