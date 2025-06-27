<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class AtribuirPermissoesSeeder extends Seeder
{
    public function run()
    {
        // Opcional: limpa o cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Busca o usuário (por ID, email ou outro critério)
        $usuario = User::where('email', 'master@teste.com')->first();

        // Lista de permissões novas
        $novasPermissoes = [
            'gerenciar preferencias',
            'gerenciar dayuse',
            'vender dayuse',
            'cadastrar aluguel',
            'gerenciar aluguel',
            'hotel',
            'gerenciar NFe'
        ];

        // Garante que as permissões existam e as atribui ao usuário
        foreach ($novasPermissoes as $permissao) {
            $perm = Permission::firstOrCreate(['name' => $permissao]);
            $usuario->givePermissionTo($perm);
        }
    }
}