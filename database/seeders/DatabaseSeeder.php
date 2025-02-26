<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Master']);
        Role::firstOrCreate(['name' => 'financeiro']);
        Role::firstOrCreate(['name' => 'funcionario']);

        $masterUser = User::firstOrCreate([
            'email' => 'master@teste.com',
        ], [
            'name' => 'Master Admin',
            'password' => bcrypt('12345678')
        ]);
        $masterUser->assignRole('Master');
        $masterUser->givePermissionTo('gerenciar usuarios');

    }

}
