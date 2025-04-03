<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cfop extends Model
{
    use HasFactory;
    protected $fillable = [
        'natureza',
        'cfop',
    ];
}
