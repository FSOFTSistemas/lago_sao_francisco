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
            'recebimento-cartão-crédito',
            'recebimento-cartão-débito',
            'recebimento-pix',
            'recebimento-carteira',
            'recebimento-cheque',
            'recebimento-transferência-bancária',
            'sangria',
            'suprimento',
            'abertura de caixa',
            'fechamento de caixa',
            'cancelamento-dinheiro',
            'cancelamento-cartão-crédito',
            'cancelamento-cartão-débito',
            'cancelamento-pix',
            'cancelamento-transferência-bancária',
            'cancelamento-boleto-bancário',
            'cancelamento-carteira',
            'cancelamento-cheque',
            'cancelamento-sympla',
            'pagamento-conta-corrente',
            'pagamento-caixa',
        ];

        foreach ($movimentos as $descricao) {
            Movimento::firstOrCreate([
                'descricao' => $descricao,
            ]);
        }
    }
}
