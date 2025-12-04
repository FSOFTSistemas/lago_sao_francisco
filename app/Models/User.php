<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 *
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method \Illuminate\Support\Collection getAllPermissions()
 * @method bool hasRole(string|int|array|\Spatie\Permission\Models\Role $role, string|null $guard = null)
 * @method bool hasAnyRole(string|int|array|\Spatie\Permission\Models\Role ...$roles)
 * @method bool hasAllRoles(string|int|array|\Spatie\Permission\Models\Role ...$roles)
 * @method \App\Models\User assignRole(string|int|array|\Spatie\Permission\Models\Role ...$roles)
 * @method \App\Models\User removeRole(string|\Spatie\Permission\Models\Role $role)
 * @method bool hasPermissionTo(string|int|\Spatie\Permission\Models\Permission $permission, string|null $guardName = null)
 * @method \App\Models\User givePermissionTo(string|int|array|\Spatie\Permission\Models\Permission ...$permissions)
 * @method \App\Models\User revokePermissionTo(string|int|\Spatie\Permission\Models\Permission $permission)
 * @method \App\Models\User syncPermissions(array|\Spatie\Permission\Models\Permission[] ...$permissions)
 * @method bool hasDirectPermission(string|int|\Spatie\Permission\Models\Permission $permission)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'empresa_id',
        'ativo',
    ];

        public function empresa()
        {
            return $this->belongsTo(Empresa::class, 'empresa_id');
        }
}
