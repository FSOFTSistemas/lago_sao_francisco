<?php

namespace Database\Seeders;

use App\Models\CategoriasCardapio;
use App\Models\CategoriasDeItensCardapio;
use App\Models\ItensDoCardapio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaCardapioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Salgados de forno',
            'Salgados fritos',
            'Finger food',
            'Sobremesa',
            'Bebidas',
            'Canapés'
        ];
        foreach ($categorias as $categoria) {
            CategoriasDeItensCardapio::firstOrCreate(['nome' => $categoria]);
        }
    }
}
