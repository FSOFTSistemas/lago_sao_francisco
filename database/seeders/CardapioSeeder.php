<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CardapioSeeder extends Seeder
{
    public $cardapios;
    public function run(): void
    {
        $cardapios = [
            [
                'NomeCardapio' => 'Cardápio Bubu Princes',
                'AnoCardapio' => 2025,
                'ValidadeOrcamentoDias' => 30,
                'PoliticaCriancaGratisLimiteIdade' => 6,
                'PoliticaCriancaDescontoPercentual' => 50.00,
                'PoliticaCriancaDescontoIdadeInicio' => 7,
                'PoliticaCriancaDescontoIdadeFim' => 12,
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 13,
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'NomeCardapio' => 'Cardápio Lady Laura',
                'AnoCardapio' => 2025,
                'ValidadeOrcamentoDias' => 30,
                'PoliticaCriancaGratisLimiteIdade' => 6,
                'PoliticaCriancaDescontoPercentual' => 50.00,
                'PoliticaCriancaDescontoIdadeInicio' => 7,
                'PoliticaCriancaDescontoIdadeFim' => 12,
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 13,
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'NomeCardapio' => 'Cardápio Infantil',
                'AnoCardapio' => 2025,
                'ValidadeOrcamentoDias' => 15,
                'PoliticaCriancaGratisLimiteIdade' => 5,
                'PoliticaCriancaDescontoPercentual' => 50.00,
                'PoliticaCriancaDescontoIdadeInicio' => 6,
                'PoliticaCriancaDescontoIdadeFim' => 10,
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 1,
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'NomeCardapio' => 'Cardápio Buffet Vegetariano',
                'AnoCardapio' => 2024,
                'ValidadeOrcamentoDias' => 10,
                'PoliticaCriancaGratisLimiteIdade' => 6,
                'PoliticaCriancaDescontoPercentual' => 30.00,
                'PoliticaCriancaDescontoIdadeInicio' => 7,
                'PoliticaCriancaDescontoIdadeFim' => 12,
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 13,
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'NomeCardapio' => 'Cardápio Aniversário Econômico',
                'AnoCardapio' => 2024,
                'ValidadeOrcamentoDias' => 5,
                'PoliticaCriancaGratisLimiteIdade' => 3,
                'PoliticaCriancaDescontoPercentual' => 40.00,
                'PoliticaCriancaDescontoIdadeInicio' => 4,
                'PoliticaCriancaDescontoIdadeFim' => 8,
                'PoliticaCriancaPrecoIntegralIdadeInicio' => 9,
                'PossuiOpcaoEscolhaConteudoPrincipalRefeicao' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Inserir e obter os IDs dos cardápios
        $cardapioIds = [];
        foreach ($cardapios as $cardapio) {
            $id = DB::table('cardapios')->insertGetId($cardapio);
            $cardapioIds[] = $id;
        }

        // Retornar os IDs para uso em outros seeders
        $this->cardapios = $cardapioIds;
    }
}