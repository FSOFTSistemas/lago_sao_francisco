<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'gerenciar usuarios']);

        $master = User::firstOrCreate(
            ['email' => 'master@teste.com'],
            [
                'name' => 'Master',
                'password' => bcrypt('12345678'),
            ]
        );

        $master->givePermissionTo('gerenciar usuarios');
    }
    }
