<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\ContaCorrente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BancoSeeder extends Seeder
{
    public $banco;
    public $contaCorrente;
    public function run(): void
    {
        $banco = Banco::create([
            'descricao' => 'Banco Teste',
            'agencia' => '0000',
            'numero_banco' => '0000',
            'numero_conta' => '00000',
            'digito_numero' => '0',
            'digito_agencia' => '0',
            'digito_conta' => '0',
            'agencia_uf' => 'PE',
            'agencia_cidade' => 'Garanhuns',
            'taxa' => 0
        ]);

        $contaCorrente = ContaCorrente::create([
            'descricao' => 'Conta Corrente Teste',
            'numero_conta' => '00000',
            'titular' => 'Teste',
            'saldo' => 5000,
            'banco_id' => 1
        ]);

        $this->banco = $banco;
        $this->contaCorrente = $contaCorrente;
    }
}
