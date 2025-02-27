<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'gerenciar usuarios']);
        Role::firstOrCreate(['name' => 'Master']);
        Role::firstOrCreate(['name' => 'financeiro']);
        Role::firstOrCreate(['name' => 'funcionario']);
        $empresa = Empresa::firstOrCreate([
            'razao_social' => 'Empresa Teste',
            'nome_fantasia' => 'Empresa Teste',
            'cnpj' => '12345678901234',
            'endereco' => null,
            'inscricao_estadual' => '12345678901234',
        ]);

        $masterUser = User::firstOrCreate([
            'email' => 'master@teste.com',
            'name' => 'Master Admin',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresa->id,

        ]);
        $masterUser->assignRole('Master');
        $masterUser->givePermissionTo('gerenciar usuarios');

    }

}
