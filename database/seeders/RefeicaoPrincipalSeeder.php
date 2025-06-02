<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefeicaoPrincipalSeeder extends Seeder
{
    // Variáveis para cada refeição
    public $miniMacarrao;
    public $nuggetsFrango;
    public $miniPizza;
    public $fileMignon;
    public $salmaoGrelhado;
    public $risotoCogumelos;
    public $lasanhaBerinjela;
    public $quicheLegumes;
    public $lancheCompleto;
    public $miniHamburguer;

    public $cardapio1_id;
    public $cardapio2_id;
    public $cardapio3_id;
    public $cardapio4_id;

    public function run(): void
    {

        $cardapio1_id = $this->cardapio1_id;
        $cardapio2_id = $this->cardapio2_id;
        $cardapio3_id = $this->cardapio3_id;
        $cardapio4_id = $this->cardapio4_id;


        $refeicoesPrincipais = [
            // Pratos Principais (Festa Infantil)
            [
                'NomeOpcaoRefeicao' => 'Mini Porções de Macarrão',
                'PrecoPorPessoa' => 25.00,
                'DescricaoOpcaoRefeicao' => 'Macarrão com molho à bolonhesa em porções individuais',
                'cardapio_id' => $cardapio1_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Nuggets de Frango',
                'PrecoPorPessoa' => 22.00,
                'DescricaoOpcaoRefeicao' => 'Nuggets de frango empanados e crocantes',
                'cardapio_id' => $cardapio1_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Mini Pizza',
                'PrecoPorPessoa' => 20.00,
                'DescricaoOpcaoRefeicao' => 'Pizzas individuais com diversos sabores',
                'cardapio_id' => $cardapio1_id
            ],

            // Prato do Dia (Casamento Premium)
            [
                'NomeOpcaoRefeicao' => 'Filé Mignon ao Molho Madeira',
                'PrecoPorPessoa' => 85.00,
                'DescricaoOpcaoRefeicao' => 'Filé mignon suculento com molho madeira e acompanhamentos',
                'cardapio_id' => $cardapio2_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Salmão Grelhado com Molho de Ervas',
                'PrecoPorPessoa' => 78.00,
                'DescricaoOpcaoRefeicao' => 'Salmão fresco grelhado com molho especial de ervas',
                'cardapio_id' => $cardapio2_id
            ],

            // Pratos Vegetarianos (Corporativo Standard)
            [
                'NomeOpcaoRefeicao' => 'Risoto de Cogumelos',
                'PrecoPorPessoa' => 35.00,
                'DescricaoOpcaoRefeicao' => 'Risoto cremoso com cogumelos frescos',
                'cardapio_id' => $cardapio3_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Lasanha de Berinjela',
                'PrecoPorPessoa' => 32.00,
                'DescricaoOpcaoRefeicao' => 'Lasanha vegetariana com berinjela e queijos',
                'cardapio_id' => $cardapio3_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Quiche de Legumes',
                'PrecoPorPessoa' => 30.00,
                'DescricaoOpcaoRefeicao' => 'Quiche assado com variedade de legumes',
                'cardapio_id' => $cardapio3_id
            ],

            // Lanches (Infantil)
            [
                'NomeOpcaoRefeicao' => 'Lanche Completo',
                'PrecoPorPessoa' => 18.00,
                'DescricaoOpcaoRefeicao' => 'Sanduíche, suco e fruta',
                'cardapio_id' => $cardapio4_id
            ],
            [
                'NomeOpcaoRefeicao' => 'Mini Hambúrguer',
                'PrecoPorPessoa' => 15.00,
                'DescricaoOpcaoRefeicao' => 'Hambúrguer em pão pequeno com queijo',
                'cardapio_id' => $cardapio4_id
            ]
        ];

        // Inserindo cada refeição e atribuindo o ID à variável correspondente
        $this->miniMacarrao = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[0]);
        $this->nuggetsFrango = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[1]);
        $this->miniPizza = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[2]);
        $this->fileMignon = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[3]);
        $this->salmaoGrelhado = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[4]);
        $this->risotoCogumelos = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[5]);
        $this->lasanhaBerinjela = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[6]);
        $this->quicheLegumes = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[7]);
        $this->lancheCompleto = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[8]);
        $this->miniHamburguer = DB::table('refeicao_principals')->insertGetId($refeicoesPrincipais[9]);
    }
}