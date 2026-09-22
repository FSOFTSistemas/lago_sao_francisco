<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name'       => 'gerenciar almoxarifado',
            'guard_name' => 'web',
        ]);

        $masterRole = Role::where('name', 'Master')->first();
        if ($masterRole) {
            $masterRole->givePermissionTo($permission);
        }

        try {
            $masterUsers = User::role('Master')->get();
            foreach ($masterUsers as $user) {
                $user->givePermissionTo($permission);
            }
        } catch (\Throwable $e) {
            // Em ambientes isolados ou seeds iniciais
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'gerenciar almoxarifado')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
