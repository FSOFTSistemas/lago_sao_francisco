<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItensCardapioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        // Arrays com nomes reais de itens de cardápio
        $entradas = [
            'Bruschetta de Tomate',
            'Carpaccio de Carne',
            'Salada Caprese',
            'Bolinhos de Bacalhau',
            'Creme de Abóbora',
            'Tábua de Frios',
            'Canapés Variados',
            'Ceviche de Peixe',
            'Pastel de Queijo',
            'Coxinha de Frango'
        ];

        $pratosPrincipais = [
            'Filé Mignon com Molho Madeira',
            'Risoto de Cogumelos',
            'Frango à Parmegiana',
            'Peixe Grelhado com Legumes',
            'Lasanha à Bolonhesa',
            'Strogonoff de Carne',
            'Penne ao Molho Pesto',
            'Bife Ancho com Purê de Batata',
            'Moqueca de Peixe',
            'Frango com Quiabo',
            'Feijoada Completa',
            'Picanha na Chapa',
            'Espaguete Carbonara',
            'Bobó de Camarão',
            'Ratatouille'
        ];

        $sobremesas = [
            'Petit Gateau',
            'Tiramisù',
            'Cheesecake de Frutas Vermelhas',
            'Mousse de Chocolate',
            'Pudim de Leite',
            'Sorvete Artesanal',
            'Torta de Limão',
            'Brigadeiro Gourmet',
            'Creme Brulee',
            'Panacota com Calda de Frutas'
        ];

        $bebidas = [
            'Suco Natural de Laranja',
            'Caipirinha de Limão',
            'Vinho Tinto Seco',
            'Cerveja Artesanal IPA',
            'Água Mineral com Gás',
            'Refrigerante Tradicional',
            'Suco de Abacaxi com Hortelã',
            'Coquetel de Morango',
            'Café Expresso',
            'Chá Gelado de Pêssego'
        ];

        // Gerar 20 itens aleatórios
        for ($i = 0; $i < 20; $i++) {
            $tipo = $faker->randomElement(['Entrada', 'Prato Principal', 'Sobremesa', 'Bebida']);
            
            DB::table('itens_do_cardapios')->insert([
                'nome_item' => match($tipo) {
                    'Entrada' => $faker->unique()->randomElement($entradas),
                    'Prato Principal' => $faker->unique()->randomElement($pratosPrincipais),
                    'Sobremesa' => $faker->unique()->randomElement($sobremesas),
                    'Bebida' => $faker->unique()->randomElement($bebidas),
                },
                'tipo_item' => $tipo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}