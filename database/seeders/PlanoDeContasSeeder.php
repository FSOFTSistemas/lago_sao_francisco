<?php

namespace Database\Seeders;

use App\Models\PlanoDeConta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanoDeContasSeeder extends Seeder
{
    public $plano1;
    public $plano2;
    public $plano3;
    public $plano4;
    public $plano5;
    public $plano6;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planos = [
            [
                'descricao' => 'Receitas Operacionais',
                'tipo' => 'receita',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ],
            [
                'descricao' => 'Despesas Administrativas',
                'tipo' => 'despesa',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ],
            [
                'descricao' => 'Impostos e Taxas',
                'tipo' => 'despesa',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ],
            [
                'descricao' => 'Salários e Benefícios',
                'tipo' => 'despesa',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ],
            [
                'descricao' => 'Investimentos',
                'tipo' => 'receita',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ],
            [
                'descricao' => 'Abertura/Fechamento de caixa',
                'tipo' => 'receita',
                'plano_de_conta_pai' => null,
                'empresa_id' => 1
            ]
        ];

        $this->plano1 = PlanoDeConta::create($planos[0]);
        $this->plano2 = PlanoDeConta::create($planos[1]);
        $this->plano3 = PlanoDeConta::create($planos[2]);
        $this->plano4 = PlanoDeConta::create($planos[3]);
        $this->plano5 = PlanoDeConta::create($planos[4]);
        $this->plano6 = PlanoDeConta::create($planos[5]);
    }
}
