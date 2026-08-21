<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var list<string> */
    private array $canais = [
        'Booking',
        'Expedia',
        'B2B Reservas',
    ];

    public function up(): void
    {
        $agora = now();
        $empresas = DB::table('empresas')->pluck('id');

        foreach ($empresas as $empresaId) {
            foreach ($this->canais as $canal) {
                if (DB::table('funcionarios')->where('empresa_id', $empresaId)->where('nome', $canal)->exists()) {
                    continue;
                }

                DB::table('funcionarios')->insert([
                    'nome' => $canal,
                    'cpf' => null,
                    'endereco_id' => null,
                    'salario' => null,
                    'data_contratacao' => $agora->toDateString(),
                    'status' => 'ativo',
                    'setor' => 'Canais Online',
                    'cargo' => 'Canal de venda',
                    'vendedor' => true,
                    'caixa' => false,
                    'senha_supervisor' => null,
                    'empresa_id' => $empresaId,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Mantém canais para preservar histórico de reservas vinculadas.
    }
};
