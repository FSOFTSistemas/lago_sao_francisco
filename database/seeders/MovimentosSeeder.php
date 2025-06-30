<?php

namespace Database\Seeders;

use App\Models\Movimento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovimentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movimentos = [
            'venda-dinheiro',                   
            'venda-cartão-crédito',
            'venda-cartão-débito',
            'venda-pix',
            'venda-transferência-bancária',
            'venda-boleto-bancário',
            'venda-carteira',
            'venda-cheque',
            'venda-sympla',
            'recebimento-dinheiro',
            'recebimento-cartão',
            'recebimento-pix',
            'recebimento-carteira',
            'recebimento-cheque',
            'sangria',
            'suprimento',
            'abertura de caixa',
            'fechamento de caixa'
        ];

        foreach ($movimentos as $descricao) {
            Movimento::firstOrCreate([
                'descricao' => $descricao,
            ]);
        }
    }
}
