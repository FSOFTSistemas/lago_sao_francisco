<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItensCardapioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        // Define os itens organizados por categoria
        $itensPorCategoria = [
            'Salgadinhos' => ['Coxinha de Frango', 'Risoles de Queijo', 'Bolinha de Queijo', 'Kibe Frito'],
            'Mini Sanduíches' => ['Mini Hambúrguer', 'Mini Sanduíche Natural', 'Mini Doguinho'],

            'Pratos Infantis' => ['Macarrão com Queijo', 'Mini Hambúrguer com Batata', 'Nuggets com Arroz'],
            'Massas' => ['Lasanha à Bolonhesa', 'Espaguete Carbonara', 'Penne ao Molho Pesto'],

            'Doces' => ['Brigadeiro Gourmet', 'Beijinho', 'Mini Churros', 'Gelatina Colorida'],
            'Bolo' => ['Bolo de Chocolate', 'Bolo de Cenoura com Cobertura'],

            'Prato Principal' => ['Filé Mignon com Molho Madeira', 'Frango à Parmegiana', 'Bobó de Camarão'],
            'Guarnições' => ['Arroz Branco', 'Purê de Batata', 'Farofa', 'Legumes no Vapor'],
            'Molhos' => ['Molho Madeira', 'Molho Branco', 'Molho Pesto'],

            'Saladas Verdes' => ['Alface com Rúcula', 'Mix de Folhas com Tomate Cereja'],
            'Saladas Compostas' => ['Salada de Batata com Maionese', 'Salada de Grão de Bico'],

            'Pratos Quentes' => ['Ratatouille', 'Escondidinho de Legumes'],
            'Pratos Frios' => ['Tabule', 'Salada de Lentilha'],

            'Sucos de Frutas' => ['Suco de Laranja Natural', 'Suco de Abacaxi com Hortelã'],

            'Lanches Tradicionais' => ['Pão de Queijo', 'Mini Pizza', 'Empadinha de Frango'],
            'Porções Individuais' => ['Mini Porção de Batata Frita', 'Mini Porção de Nuggets'],

            'Sucos' => ['Suco de Uva Integral', 'Suco de Maçã'],
            'Vitaminas' => ['Vitamina de Banana', 'Vitamina de Morango'],

            'Vinhos' => ['Vinho Tinto Seco', 'Vinho Branco Suave'],
            'Cervejas' => ['Cerveja Pilsen', 'Cerveja IPA'],

            'Refrigerantes' => ['Coca-Cola', 'Guaraná'],
            'Águas' => ['Água com Gás', 'Água sem Gás'],

            'Coquetéis' => ['Caipirinha de Limão', 'Coquetel de Morango'],
            'Drinks Sem Álcool' => ['Mocktail de Frutas', 'Chá Gelado de Pêssego'],
        ];

        foreach ($itensPorCategoria as $categoria => $itens) {
            foreach ($itens as $item) {
                DB::table('itens_do_cardapios')->insert([
                    'nome_item' => $item,
                    'tipo_item' => $categoria, // Aqui o tipo_item será o nome da categoria para facilitar o relacionamento depois
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
