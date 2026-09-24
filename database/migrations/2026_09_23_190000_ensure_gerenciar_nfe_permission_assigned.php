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
            'name' => 'gerenciar NFe',
            'guard_name' => 'web',
        ]);

        // No sistema Lago, as permissões são geridas individualmente por usuário (model_has_permissions).
        // Se a permissão estiver vinculada à role Master (role_has_permissions), qualquer usuário
        // com papel Master herda a permissão mesmo se desmarcada no formulário de Usuários.
        $masterRole = Role::where('name', 'Master')->first();
        if ($masterRole && $masterRole->hasPermissionTo($permission)) {
            $masterRole->revokePermissionTo($permission);
        }

        // Garante que o administrador Master principal (master@teste.com) possua a permissão direta
        $masterUser = User::where('email', 'master@teste.com')->first();
        if ($masterUser && ! $masterUser->hasPermissionTo($permission)) {
            $masterUser->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
