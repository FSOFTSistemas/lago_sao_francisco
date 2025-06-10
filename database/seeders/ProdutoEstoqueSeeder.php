<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaProduto;
use App\Models\Produto;
use App\Models\Estoque;

class ProdutoEstoqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primeiro cria as categorias
        $categorias = [
            1 => 'Bebidas',
            2 => 'Snacks',
            3 => 'Higiene Pessoal',
            4 => 'Produtos de Emergência',
            5 => 'Alimentos Prontos',
        ];

        foreach ($categorias as $id => $descricao) {
            CategoriaProduto::firstOrCreate(['id' => $id], ['descricao' => $descricao]);
        }

        // Em seguida cria os produtos
        $produtos = [
            [
                'descricao' => 'Coca-Cola 350ml',
                'categoria_produto_id' => 1,
                'ativo' => true,
                'ean' => '7894900011517',
                'preco_custo' => 2.50,
                'preco_venda' => 4.00,
                'ncm' => '22021000',
                'cst' => '00',
                'cfop_interno' => '5102',
                'cfop_externo' => '6102',
                'aliquota' => 18,
                'csosn' => '102',
                'empresa_id' => 1,
                'comissao' => 2,
                'observacoes' => 'Refrigerante lata',
            ],
            [
                'descricao' => 'Água Mineral 500ml',
                'categoria_produto_id' => 1,
                'ativo' => true,
                'ean' => '7891234560001',
                'preco_custo' => 1.00,
                'preco_venda' => 2.50,
                'ncm' => '22011000',
                'cst' => '00',
                'cfop_interno' => '5102',
                'cfop_externo' => '6102',
                'aliquota' => 0,
                'csosn' => '102',
                'empresa_id' => 1,
                'comissao' => 2,
                'observacoes' => 'Sem gás',
            ],
            // ... demais produtos ...
        ];

        foreach ($produtos as $produtoData) {
            $produto = Produto::create($produtoData);

            Estoque::create([
                'produto_id' => $produto->id,
                'estoque_atual' => rand(5, 50),
                'empresa_id' => $produto->empresa_id,
                'entradas' => rand(10, 100),
                'saidas' => rand(0, 30),
            ]);
        }
    }
}
