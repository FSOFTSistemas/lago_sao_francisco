<?php

namespace Database\Seeders;

use App\Models\Funcionario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FuncionariosSeeder extends Seeder
{
    public $funcionario1;
    public $funcionario2;
    public $funcionario3;
    public $funcionario4;
    public $funcionario5;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $funcionarios = [
            [
                'nome' => 'Carlos Silva',
                'cpf' => '213.456.789-01',
                'endereco_id' => null,
                'salario' => 3500.00,
                'data_contratacao' => Carbon::now()->subYears(3),
                'status' => 'ativo',
                'setor' => 'Financeiro',
                'cargo' => 'Analista Financeiro',
                'empresa_id' => 1
            ],
            [
                'nome' => 'Ana Souza',
                'cpf' => '997.654.321-00',
                'endereco_id' => null,
                'salario' => 4200.00,
                'data_contratacao' => Carbon::now()->subYears(5),
                'status' => 'ativo',
                'setor' => 'Recursos Humanos',
                'cargo' => 'Gerente de RH',
                'empresa_id' => 1
            ],
            [
                'nome' => 'Mariana Lopes',
                'cpf' => '331.010.687-01',
                'endereco_id' => null,
                'salario' => 2800.00,
                'data_contratacao' => Carbon::now()->subYears(2),
                'status' => 'ativo',
                'setor' => 'Marketing',
                'cargo' => 'Coordenadora de Marketing',
                'empresa_id' => 2
            ],
            [
                'nome' => 'Fernando Almeida',
                'cpf' => '466.709.123-00',
                'endereco_id' => null,
                'salario' => 5000.00,
                'data_contratacao' => Carbon::now()->subYears(6),
                'status' => 'ativo',
                'setor' => 'TI',
                'cargo' => 'Desenvolvedor Sênior',
                'empresa_id' => 2
            ],
            [
                'nome' => 'Beatriz Oliveira',
                'cpf' => '771.123.456-00',
                'endereco_id' => null,
                'salario' => 3300.00,
                'data_contratacao' => Carbon::now()->subYears(4),
                'status' => 'ativo',
                'setor' => 'Vendas',
                'cargo' => 'Executiva de Contas',
                'empresa_id' => 3
            ],
            [
                'nome' => 'Booking',
                'cpf' => null,
                'endereco_id' => null,
                'salario' => null,
                'data_contratacao' => Carbon::now(),
                'status' => 'ativo',
                'setor' => 'Canais Online',
                'cargo' => 'Canal de venda',
                'vendedor' => true,
                'empresa_id' => 1
            ],
            [
                'nome' => 'Expedia',
                'cpf' => null,
                'endereco_id' => null,
                'salario' => null,
                'data_contratacao' => Carbon::now(),
                'status' => 'ativo',
                'setor' => 'Canais Online',
                'cargo' => 'Canal de venda',
                'vendedor' => true,
                'empresa_id' => 1
            ],
            [
                'nome' => 'B2B Reservas',
                'cpf' => null,
                'endereco_id' => null,
                'salario' => null,
                'data_contratacao' => Carbon::now(),
                'status' => 'ativo',
                'setor' => 'Canais Online',
                'cargo' => 'Canal de venda',
                'vendedor' => true,
                'empresa_id' => 1
            ],
            ];

            foreach ($funcionarios as $indice => $funcionario) {
                $criado = Funcionario::firstOrCreate(
                    [
                        'nome' => $funcionario['nome'],
                        'empresa_id' => $funcionario['empresa_id'],
                    ],
                    $funcionario,
                );

                if ($indice < 5) {
                    $this->{'funcionario'.($indice + 1)} = $criado;
                }
            }
        }
}
