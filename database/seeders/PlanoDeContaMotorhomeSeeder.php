<?php

namespace Database\Seeders;

use App\Models\PlanoDeConta;
use Illuminate\Database\Seeder;

class PlanoDeContaMotorhomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotente e seguro para rodar isoladamente em bancos já existentes
     * (produção) sem duplicar as contas base, ao contrário de PlanoDeContasSeeder.
     */
    public function run(): void
    {
        $receitasOperacionais = PlanoDeConta::where('descricao', 'Receitas Operacionais')
            ->whereNull('plano_de_conta_pai')
            ->value('id');

        PlanoDeConta::updateOrCreate(
            ['descricao' => 'Motorhome'],
            [
                'tipo' => 'receita',
                'plano_de_conta_pai' => $receitasOperacionais,
                'empresa_id' => 1,
            ]
        );
    }
}
