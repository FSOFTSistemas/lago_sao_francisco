<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisponibilidadeItemCategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = DB::table('categorias_de_itens_cardapios')->get();
        $itens = DB::table('itens_do_cardapios')->get();

        foreach ($categorias as $categoria) {
            // Busca itens cujo tipo_item bate com o nome da categoria
            $itensRelacionados = $itens->where('tipo_item', $categoria->nome_categoria_item);

            // Seleciona até 5 itens dessa categoria
            $itensSelecionados = $itensRelacionados->count() > 0
                ? $itensRelacionados->random(min(5, $itensRelacionados->count()))
                : collect();

            foreach ($itensSelecionados as $index => $item) {
                DB::table('disponibilidade_item_categorias')->insert([
                    'ItemInclusoPadrao' => rand(0, 1),
                    'OrdemExibicao' => $index + 1,
                    'CategoriaItemID' => $categoria->id,
                    'ItemID' => $item->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
