<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormaPagamento extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formasPagamento = [
            [
                'descricao' => 'Cartão de Crédito',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Cartão de Débito',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descricao' => 'Boleto Bancário',
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
                'descricao' => 'Transferência Bancária',
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

        $this->command->info('Formas de pagamento semeadas com sucesso.');
    }
}
