<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            $receitasOperacionaisId = DB::table('plano_de_contas')
                ->where('descricao', 'Receitas Operacionais')
                ->where('tipo', 'receita')
                ->where('empresa_id', $empresaId)
                ->value('id');

            if (! $receitasOperacionaisId) {
                $receitasOperacionaisId = DB::table('plano_de_contas')->insertGetId([
                    'descricao' => 'Receitas Operacionais',
                    'tipo' => 'receita',
                    'plano_de_conta_pai' => null,
                    'empresa_id' => $empresaId,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }

            $excursaoId = DB::table('plano_de_contas')
                ->where('descricao', 'Excursões')
                ->where('tipo', 'receita')
                ->where('empresa_id', $empresaId)
                ->value('id');

            if ($excursaoId) {
                DB::table('plano_de_contas')
                    ->where('id', $excursaoId)
                    ->update([
                        'plano_de_conta_pai' => $receitasOperacionaisId,
                        'updated_at' => $agora,
                    ]);

                continue;
            }

            DB::table('plano_de_contas')->insert([
                'descricao' => 'Excursões',
                'tipo' => 'receita',
                'plano_de_conta_pai' => $receitasOperacionaisId,
                'empresa_id' => $empresaId,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }
    }

    public function down(): void
    {
        // Mantém a classificação contábil para não remover planos que possam ter sido usados.
    }
};
