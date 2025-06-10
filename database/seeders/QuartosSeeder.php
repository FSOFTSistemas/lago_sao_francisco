<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Quarto;

class QuartosSeeder extends Seeder
{
    public function run(): void
    {
        // Criar 2 categorias
        $categoria1 = Categoria::create([
            'titulo' => 'Categoria Luxo',
            'ocupantes' => '2',
            'descricao' => 'Quarto de categoria luxo com vista para o mar',
            'status' => true,
            'posicao' => 'Andar 1',
        ]);

        $categoria2 = Categoria::create([
            'titulo' => 'Categoria Econômica',
            'ocupantes' => '1',
            'descricao' => 'Quarto econômico ideal para viajantes individuais',
            'status' => true,
            'posicao' => 'Andar 2',
        ]);

        // Gerar 10 quartos, alternando entre as categorias
        for ($i = 1; $i <= 10; $i++) {
            Quarto::create([
                'nome' => 'Quarto ' . $i,
                'descricao' => 'Descrição do quarto ' . $i,
                'posicao' => 'Andar ' . (($i % 2) + 1),
                'status' => ($i % 2 == 0), // true para pares, false para ímpares
                'categoria_id' => ($i % 2 == 0) ? $categoria2->id : $categoria1->id,
            ]);
        }
    }
}
