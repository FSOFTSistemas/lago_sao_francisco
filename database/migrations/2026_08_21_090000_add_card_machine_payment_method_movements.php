<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var list<string> */
    private array $movimentos = [
        'venda-maquineta-de-cartao',
        'recebimento-maquineta-de-cartao',
        'cancelamento-maquineta-de-cartao',
    ];

    public function up(): void
    {
        foreach ($this->movimentos as $descricao) {
            if (! DB::table('movimentos')->where('descricao', $descricao)->exists()) {
                DB::table('movimentos')->insert([
                    'descricao' => $descricao,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $movimentosEmUso = DB::table('fluxo_caixas')
            ->whereIn('movimento_id', function ($query) {
                $query->select('id')
                    ->from('movimentos')
                    ->whereIn('descricao', $this->movimentos);
            })
            ->exists();

        if (! $movimentosEmUso) {
            DB::table('movimentos')
                ->whereIn('descricao', $this->movimentos)
                ->delete();
        }
    }
};
