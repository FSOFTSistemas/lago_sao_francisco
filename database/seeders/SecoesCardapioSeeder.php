<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecoesCardapioSeeder extends Seeder
{
    public $cardapio1_id;
    public $cardapio2_id;
    public $cardapio3_id;
    public $cardapio4_id;
    public $cardapio5_id;

    public $secoes;
    
    public function run(): void
    {
        $cardapio1_id = $this->cardapio1_id;
        $cardapio2_id = $this->cardapio2_id;
        $cardapio3_id = $this->cardapio3_id;
        $cardapio4_id = $this->cardapio4_id;
        $cardapio5_id = $this->cardapio5_id;
        

        // Agora as seções de cardápio para 5 cardápios
        $secoes = [
            // Cardápio 1 (Cardápio Festa Infantil)
            [
                'nome_secao_cardapio' => 'Entradas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 1,
                'cardapio_id' => $cardapio1_id
            ],
            [
                'nome_secao_cardapio' => 'Pratos Principais',
                'opcao_conteudo_principal_refeicao' => true,
                'ordem_exibicao' => 2,
                'cardapio_id' => $cardapio1_id
            ],
            [
                'nome_secao_cardapio' => 'Sobremesas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 3,
                'cardapio_id' => $cardapio1_id
            ],
            
            // Cardápio 2 (Cardápio Casamento Premium)
            [
                'nome_secao_cardapio' => 'Prato do Dia',
                'opcao_conteudo_principal_refeicao' => true,
                'ordem_exibicao' => 1,
                'cardapio_id' => $cardapio2_id
            ],
            [
                'nome_secao_cardapio' => 'Acompanhamentos',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 2,
                'cardapio_id' => $cardapio2_id
            ],
            
            // Cardápio 3 (Cardápio Corporativo Standard)
            [
                'nome_secao_cardapio' => 'Saladas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 1,
                'cardapio_id' => $cardapio3_id
            ],
            [
                'nome_secao_cardapio' => 'Pratos Vegetarianos',
                'opcao_conteudo_principal_refeicao' => true,
                'ordem_exibicao' => 2,
                'cardapio_id' => $cardapio3_id
            ],
            [
                'nome_secao_cardapio' => 'Sucos Naturais',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 3,
                'cardapio_id' => $cardapio3_id
            ],
            
            // Cardápio 4 (Infantil)
            [
                'nome_secao_cardapio' => 'Lanches',
                'opcao_conteudo_principal_refeicao' => true,
                'ordem_exibicao' => 1,
                'cardapio_id' => $cardapio4_id
            ],
            [
                'nome_secao_cardapio' => 'Porções Pequenas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 2,
                'cardapio_id' => $cardapio4_id
            ],
            [
                'nome_secao_cardapio' => 'Bebidas Infantis',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 3,
                'cardapio_id' => $cardapio4_id
            ],
            
            // Cardápio 5 (Bebidas)
            [
                'nome_secao_cardapio' => 'Bebidas Alcoólicas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 1,
                'cardapio_id' => $cardapio5_id
            ],
            [
                'nome_secao_cardapio' => 'Bebidas Não-Alcoólicas',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 2,
                'cardapio_id' => $cardapio5_id
            ],
            [
                'nome_secao_cardapio' => 'Drinks Especiais',
                'opcao_conteudo_principal_refeicao' => false,
                'ordem_exibicao' => 3,
                'cardapio_id' => $cardapio5_id
            ]
        ];


        $sessoesIds = [];
        foreach ($secoes as $sessao) {
            $id = DB::table('secoes_cardapios')->insertGetId($sessao);
            $sessoesIds[] = $id;
        }
        $this->secoes = $sessoesIds;
    }
}