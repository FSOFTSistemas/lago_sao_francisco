<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'];

    /**
     * Relacionamento muitos-para-muitos com permissões
     */
//     public function permissions()
//     {
//         return $this->belongsToMany(Permission::class, 'permission_tables');
//     }
}
