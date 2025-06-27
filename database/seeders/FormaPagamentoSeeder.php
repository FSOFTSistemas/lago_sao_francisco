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
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Cartão-débito',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Boleto-bancário',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'PIX',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Transferência-bancária',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Dinheiro',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('forma_pagamentos')->insert($formasPagamento);

        //$this->command->info('Formas de pagamento semeadas com sucesso.');
    }
}
