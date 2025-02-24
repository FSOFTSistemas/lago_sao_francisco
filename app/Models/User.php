<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    /**
     * Relacionamento muitos-para-muitos com permissões
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }
}
