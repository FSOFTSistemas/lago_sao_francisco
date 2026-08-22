<?php

namespace Database\Seeders;

use App\Support\ApiPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ApiPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (ApiPermissions::all() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $master = Role::firstOrCreate([
            'name' => 'Master',
            'guard_name' => 'web',
        ]);
        $financeiro = Role::firstOrCreate([
            'name' => 'financeiro',
            'guard_name' => 'web',
        ]);

        // Master representa a dona no aplicativo e nunca recebe escrita por este seeder.
        $master->revokePermissionTo(ApiPermissions::financialWrite());
        $master->givePermissionTo(ApiPermissions::ownerReadOnly());

        $financeiro->givePermissionTo(ApiPermissions::financial());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
