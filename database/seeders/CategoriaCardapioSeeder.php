<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaCardapioSeeder extends Seeder
{
    public $sessao_cardapio1_id; // Entradas (Festa Infantil)
    public $sessao_cardapio2_id; // Pratos Principais (Festa Infantil)
    public $sessao_cardapio3_id; // Sobremesas (Festa Infantil)
    public $sessao_cardapio4_id; // Prato do Dia (Casamento Premium)
    public $sessao_cardapio5_id; // Acompanhamentos (Casamento Premium)
    public $sessao_cardapio6_id; // Saladas (Corporativo Standard)
    public $sessao_cardapio7_id; // Pratos Vegetarianos (Corporativo Standard)
    public $sessao_cardapio8_id; // Sucos Naturais (Corporativo Standard)
    public $sessao_cardapio9_id; // Lanches (Infantil)
    public $sessao_cardapio10_id; // Porções Pequenas (Infantil)
    public $sessao_cardapio11_id; // Bebidas Infantis (Infantil)
    public $sessao_cardapio12_id; // Bebidas Alcoólicas (Bebidas)
    public $sessao_cardapio13_id; // Bebidas Não-Alcoólicas (Bebidas)
    public $sessao_cardapio14_id; // Drinks Especiais (Bebidas)

    public $refeicaop1_id;
    public $refeicaop2_id;

    public function run(): void
    {
        $sessoes = [
            $this->sessao_cardapio1_id,
            $this->sessao_cardapio2_id,
            $this->sessao_cardapio3_id,
            $this->sessao_cardapio4_id,
            $this->sessao_cardapio5_id,
            $this->sessao_cardapio6_id,
            $this->sessao_cardapio7_id,
            $this->sessao_cardapio8_id,
            $this->sessao_cardapio9_id,
            $this->sessao_cardapio10_id,
            $this->sessao_cardapio11_id,
            $this->sessao_cardapio12_id,
            $this->sessao_cardapio13_id,
            $this->sessao_cardapio14_id
        ];

        $refeicaop1 = $this->refeicaop1_id;
        $refeicaop2 = $this->refeicaop2_id;

        // Agora as categorias de itens do cardápio
        $categorias = [
            // Entradas (Festa Infantil - sessao_cardapio1_id)
            [
                'sessao_cardapio_id' => $sessoes[0],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Salgadinhos',
                'numero_escolhas_permitidas' => 3,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[0],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Mini Sanduíches',
                'numero_escolhas_permitidas' => 2,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Pratos Principais (Festa Infantil - sessao_cardapio2_id)
            [
                'sessao_cardapio_id' => $sessoes[1],
                'refeicao_principal_id' => $refeicaop1, // Standard
                'nome_categoria_item' => 'Pratos Infantis',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => true,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[1],
                'refeicao_principal_id' => $refeicaop1, // Standard
                'nome_categoria_item' => 'Massas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Sobremesas (Festa Infantil - sessao_cardapio3_id)
            [
                'sessao_cardapio_id' => $sessoes[2],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Doces',
                'numero_escolhas_permitidas' => 2,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[2],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Bolo',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => true,
                'ordem_exibicao' => 2
            ],

            // Prato do Dia (Casamento Premium - sessao_cardapio4_id)
            [
                'sessao_cardapio_id' => $sessoes[3],
                'refeicao_principal_id' => $refeicaop1, // Standard
                'nome_categoria_item' => 'Prato Principal',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => true,
                'ordem_exibicao' => 1
            ],

            // Acompanhamentos (Casamento Premium - sessao_cardapio5_id)
            [
                'sessao_cardapio_id' => $sessoes[4],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Guarnições',
                'numero_escolhas_permitidas' => 3,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[4],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Molhos',
                'numero_escolhas_permitidas' => 2,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Saladas (Corporativo Standard - sessao_cardapio6_id)
            [
                'sessao_cardapio_id' => $sessoes[5],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Saladas Verdes',
                'numero_escolhas_permitidas' => 2,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[5],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Saladas Compostas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Pratos Vegetarianos (Corporativo Standard - sessao_cardapio7_id)
            [
                'sessao_cardapio_id' => $sessoes[6],
                'refeicao_principal_id' => $refeicaop2, // Vegetariana
                'nome_categoria_item' => 'Pratos Quentes',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => true,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[6],
                'refeicao_principal_id' => $refeicaop2, // Vegetariana
                'nome_categoria_item' => 'Pratos Frios',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Sucos Naturais (Corporativo Standard - sessao_cardapio8_id)
            [
                'sessao_cardapio_id' => $sessoes[7],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Sucos de Frutas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],

            // Lanches (Infantil - sessao_cardapio9_id)
            [
                'sessao_cardapio_id' => $sessoes[8],
                'refeicao_principal_id' => $refeicaop1, // Standard
                'nome_categoria_item' => 'Lanches Tradicionais',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => true,
                'ordem_exibicao' => 1
            ],

            // Porções Pequenas (Infantil - sessao_cardapio10_id)
            [
                'sessao_cardapio_id' => $sessoes[9],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Porções Individuais',
                'numero_escolhas_permitidas' => 2,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],

            // Bebidas Infantis (Infantil - sessao_cardapio11_id)
            [
                'sessao_cardapio_id' => $sessoes[10],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Sucos',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[10],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Vitaminas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Bebidas Alcoólicas (Bebidas - sessao_cardapio12_id)
            [
                'sessao_cardapio_id' => $sessoes[11],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Vinhos',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[11],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Cervejas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Bebidas Não-Alcoólicas (Bebidas - sessao_cardapio13_id)
            [
                'sessao_cardapio_id' => $sessoes[12],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Refrigerantes',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[12],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Águas',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ],

            // Drinks Especiais (Bebidas - sessao_cardapio14_id)
            [
                'sessao_cardapio_id' => $sessoes[13],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Coquetéis',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 1
            ],
            [
                'sessao_cardapio_id' => $sessoes[13],
                'refeicao_principal_id' => null,
                'nome_categoria_item' => 'Drinks Sem Álcool',
                'numero_escolhas_permitidas' => 1,
                'eh_grupo_escolha_exclusiva' => false,
                'ordem_exibicao' => 2
            ]
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias_de_itens_cardapios')->insert($categoria);
        }
    }
}