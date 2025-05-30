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

    $tipos = ['Entrada', 'Prato Principal', 'Sobremesa', 'Bebida'];
    
    for ($i = 0; $i < 20; $i++) {
        DB::table('itens_do_cardapios')->insert([
            'nome_item' => $faker->unique()->words($faker->numberBetween(2, 5), true),
            'tipo_item' => $faker->randomElement($tipos),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}
