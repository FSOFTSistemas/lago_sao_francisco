<?php

namespace Database\Seeders;

use App\Models\ContasAPagar;
use App\Models\Fornecedor;
use App\Models\PlanoDeConta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContasAPagarSeeder extends Seeder
{
    public $empresaId;
    public function run(): void
    {
        $empresaId = $this->empresaId;
        $fornecedores = [];

        for ($i = 1; $i <= 5; $i++) {
            $fornecedores[] = Fornecedor::create([
                'razao_social' => 'Empresa Exemplo ' . $i,
                'nome_fantasia' => 'Fantasia ' . $i,
                'cnpj' => str_pad($i, 14, '0', STR_PAD_LEFT),
                'endereco' => 'Rua Teste ' . $i,
                'inscricao_estadual' => str_pad($i, 12, '1', STR_PAD_LEFT),
            ]);
        }
        $planoId = PlanoDeConta::inRandomOrder()->first()?->id ?? 1;

        // 10 contas parceladas
        for ($i = 1; $i <= 5; $i++) {
            $parcelas = rand(2, 5);
            $valorTotal = rand(500, 2000);
            $valorParcela = round($valorTotal / $parcelas, 2);
            $dataBase = Carbon::now()->subMonths(rand(0, 2));
            $fornecedorId = $fornecedores[array_rand($fornecedores)]->id;

            for ($p = 1; $p <= $parcelas; $p++) {
                ContasAPagar::create([
                    'descricao' => "Conta Parcelada $i - Parcela $p/$parcelas",
                    'valor' => $valorParcela,
                    'valor_pago' => 0,
                    'data_vencimento' => $dataBase->copy()->addMonths($p)->format('Y-m-d'),
                    'data_pagamento' => null,
                    'status' => 'pendente',
                    'empresa_id' => $empresaId,
                    'plano_de_contas_id' => $planoId,
                    'fornecedor_id' => $fornecedorId,
                    'numero_parcela' => $p,
                    'total_parcelas' => $parcelas,
                ]);
            }
        }

        // 10 contas avulsas
        for ($j = 1; $j <= 5; $j++) {
            $dataVenc = Carbon::now()->subMonths(rand(0, 1))->addDays(rand(0, 28));
            $fornecedorId = $fornecedores[array_rand($fornecedores)]->id;

            ContasAPagar::create([
                'descricao' => "Conta Avulsa $j",
                'valor' => rand(100, 1000),
                'valor_pago' => 0,
                'data_vencimento' => $dataVenc->format('Y-m-d'),
                'data_pagamento' => null,
                'status' => 'pendente',
                'empresa_id' => $empresaId,
                'plano_de_contas_id' => $planoId,
                'fornecedor_id' => $fornecedorId,
                'numero_parcela' => 1,
                'total_parcelas' => 1,
            ]);
        }
    }
}
