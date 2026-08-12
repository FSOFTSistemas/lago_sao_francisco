<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catálogo "Adicionais 2026/2027" (ilhas de festa) e "Cardápio Staff" (refeição
     * para fornecedores/staff). Idempotente (updateOrInsert por nome+ano) para poder
     * rodar com segurança em produção sem duplicar linhas.
     */
    public function up(): void
    {
        $ilhas = [
            [
                'nome' => 'Ilha de Frios',
                'descricao' => 'Frios e queijos (salame italiano, lombo defumado, peito de peru defumado, presunto, queijo gouda, provolone, prato, mussarela, do reino, gorgonzola), azeitona verde, baguetes, pão italiano artesanal, grissinis, cestas de mini pães, quiches variados, caponata de beringela, amendoim japonês e tradicional, patês, geleias, frutas tropicais.',
                'observacao_padrao' => null,
                'precos' => [2026 => 39.00, 2027 => 43.00],
            ],
            [
                'nome' => 'Ilha de Caldos',
                'descricao' => 'Caldinho de feijoada, caldinho de camarão, caldinho de macaxeira com charque, caldo de peixe, caldinho de quenga, caldo verde com calabresa. Acompanhamentos: ovos de codorna, torresmo e azeitonas.',
                'observacao_padrao' => 'Escolher 2 opções',
                'precos' => [2026 => 20.00, 2027 => 22.00],
            ],
            [
                'nome' => 'Ilha de Crepe Francês',
                'descricao' => 'Molhos: vermelho e branco, ou quatro queijos e tomate com ervas. Recheio: frango, carne, carne seca, bacalhau. Acompanhamentos: queijo, presunto, calabresa, peito de peru, palmito. Salada tropical inclusa.',
                'observacao_padrao' => 'Escolher 1 combo de molho, 2 recheios e 2 acompanhamentos',
                'precos' => [2026 => 31.00, 2027 => 35.00],
            ],
            [
                'nome' => 'Ilha de Despedida',
                'descricao' => 'Montada após o corte do bolo: café, chá, água com gás, bolo da vovó, amanteigados, mini sanduíche, torta de banana com canela.',
                'observacao_padrao' => null,
                'precos' => [2026 => 24.00, 2027 => 26.00],
            ],
            [
                'nome' => 'Ilha de Sobremesa',
                'descricao' => 'Torta de limão, torta banoffee, mousse de maracujá, creme de sonho de valsa, delícia de abacaxi.',
                'observacao_padrao' => 'Escolher 3 opções',
                'precos' => [2026 => 21.00, 2027 => 25.00],
            ],
            [
                'nome' => 'Ilha de Fast Food',
                'descricao' => 'Batata frita, mini hambúrguer, mini cachorro quente, mini churros, mini pizza, pipoca.',
                'observacao_padrao' => null,
                'precos' => [2026 => 20.00, 2027 => 25.00],
            ],
            [
                'nome' => 'Ilha Finger Food',
                'descricao' => 'Escondidinho de macaxeira com charque, escondidinho de batata doce com frango, ragu de costela, strogonoff com batata palha.',
                'observacao_padrao' => null,
                'precos' => [2026 => 22.00, 2027 => 24.00],
            ],
            [
                'nome' => 'Volante de Salgados',
                'descricao' => 'Folheado de damasco com queijo e mel, folheado de amêndoas com bacon, mini quiche de alho poró, mini quiche de queijo e presunto, mini quiche de frango com catupiry, empada de frango, pastel de forno, coxinha de frango, bolinho de queijo, croquete de bacalhau, croquete de milho, bolinho de charque, risoles de carne, risoles à portuguesa, pastel de festa.',
                'observacao_padrao' => 'Escolher 10 opções',
                'precos' => [2026 => 58.00, 2027 => 64.00],
            ],
            [
                'nome' => 'Salgados + Canapés',
                'descricao' => 'Mesmo cardápio do volante de salgados, mais canapés: dadinhos de tapioca com geleia de pimenta, camarão empanado, vol au vent de damasco com gorgonzola, vol au vent de camarão, canapé folhado com gorgonzola e manga, canapé de pesto de manjericão com salame e tomate cereja, cestinha de salaminho assado com cream cheese e azeitona, canapé de copa lombo e geleia de tomate, folheado de bacalhau.',
                'observacao_padrao' => 'Escolher 10 salgados e 5 canapés',
                'precos' => [2026 => 74.00, 2027 => 79.00],
            ],
            [
                'nome' => 'Volante de Bebidas',
                'descricao' => 'Refrigerantes (linha Coca-Cola), suco (2 opções), água sem gás, água saborizada.',
                'observacao_padrao' => null,
                'precos' => [2026 => 20.00, 2027 => 22.00],
            ],
            [
                'nome' => 'Ilha de Doces',
                'descricao' => 'Brigadeiro, beijinho, casadinho, rosadinho.',
                'observacao_padrao' => null,
                'precos' => [2026 => 17.00, 2027 => 19.00],
            ],
        ];

        // Cardápio Staff não tem valores diferentes por ano no material recebido;
        // replica o mesmo valor em 2026 e 2027 pra manter o mesmo mecanismo de seleção.
        $staff = [
            [
                'nome' => 'Refeição Staff - Buffet 1',
                'descricao' => 'Strogonofe de frango, arroz de brócolis, batata palha, salada de folhas.',
                'valor' => 29.90,
            ],
            [
                'nome' => 'Refeição Staff - Buffet 2',
                'descricao' => 'Bife ao molho madeira, arroz branco, batata gratinada, salada tropical.',
                'valor' => 39.90,
            ],
            [
                'nome' => 'Refeição Staff - Buffet 3',
                'descricao' => 'Bife ao molho madeira, frango grelhado, arroz branco, salada tropical, batata sautê.',
                'valor' => 49.90,
            ],
        ];

        foreach ($ilhas as $ilha) {
            foreach ($ilha['precos'] as $ano => $valor) {
                DB::table('pacotes_evento')->updateOrInsert(
                    ['nome' => $ilha['nome'], 'ano' => $ano],
                    [
                        'categoria' => 'ilha_adicional',
                        'descricao' => $ilha['descricao'],
                        'observacao_padrao' => $ilha['observacao_padrao'],
                        'valor' => $valor,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        foreach ($staff as $buffet) {
            foreach ([2026, 2027] as $ano) {
                DB::table('pacotes_evento')->updateOrInsert(
                    ['nome' => $buffet['nome'], 'ano' => $ano],
                    [
                        'categoria' => 'refeicao_staff',
                        'descricao' => $buffet['descricao'],
                        'observacao_padrao' => null,
                        'valor' => $buffet['valor'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('pacotes_evento')->whereIn('categoria', ['ilha_adicional', 'refeicao_staff'])->delete();
    }
};
