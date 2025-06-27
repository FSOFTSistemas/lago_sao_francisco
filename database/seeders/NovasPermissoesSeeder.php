<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class NovasPermissoesSeeder extends Seeder
{
    public function run()
    {
        $novasPermissoes = [
            'gerenciar preferencias',
            'gerenciar dayuse',
            'vender dayuse',
            'cadastrar aluguel',
            'gerenciar aluguel',
            'hotel',
            'gerenciar NFe'
        ];

        foreach ($novasPermissoes as $permissao) {
            Permission::firstOrCreate(['name' => $permissao]);
        }
    }

}