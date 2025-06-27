<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormaPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formasPagamento = [
            [
                'descricao' => 'Cartão-crédito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Cartão-débito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Boleto-bancário',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'PIX',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Transferência-bancária',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Dinheiro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Sympla',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('forma_pagamentos')->insert($formasPagamento);

        //$this->command->info('Formas de pagamento semeadas com sucesso.');
    }
}
