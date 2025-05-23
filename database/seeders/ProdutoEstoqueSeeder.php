<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;
use App\Models\Estoque;

class ProdutoEstoqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns produtos
        $produtos = [
            [
                'descricao' => 'Produto A',
                'categoria' => 2,
                'ativo' => true,
                'ean' => '1234567890123',
                'preco_custo' => 10.50,
                'preco_venda' => 15.00,
                'ncm' => '01012100',
                'cst' => '00',
                'cfop_interno' => '5101',
                'cfop_externo' => '6101',
                'aliquota' => 18,
                'csosn' => '102',
                'empresa_id' => 1,
                'comissao' => 5,
                'observacoes' => 'Produto de teste A',
            ],
            [
                'descricao' => 'Produto B',
                'categoria' => 1,
                'ativo' => true,
                'ean' => '9876543210987',
                'preco_custo' => 20.00,
                'preco_venda' => 25.00,
                'ncm' => '02013000',
                'cst' => '10',
                'cfop_interno' => '5102',
                'cfop_externo' => '6102',
                'aliquota' => 12,
                'csosn' => '103',
                'empresa_id' => 1,
                'comissao' => 3,
                'observacoes' => 'Produto de teste B',
            ],
        ];

        foreach ($produtos as $produtoData) {
            $produto = Produto::create($produtoData);

            // Criar estoque associado ao produto criado
            Estoque::create([
                'produto_id' => $produto->id,
                'estoque_atual' => rand(50, 100),
                'empresa_id' => $produto->empresa_id,
                'entradas' => rand(10, 50),
                'saidas' => rand(5, 25),
            ]);
        }
    }
}
