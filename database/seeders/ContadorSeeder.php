<?php

namespace Database\Seeders;

use App\Models\EmpresaContador;
use Illuminate\Database\Seeder;

class ContadorSeeder extends Seeder
{
    public $contador1;
    public $contador2;
    public $contador3;

    public function run(): void
    {
        $contadores = [
            [
                'cnpj' => '12.345.678/0001-99',
                'nome' => 'Carlos Silva',
                'crc' => 'CRC-123456',
                'email' => 'carlos.silva@contabilidade.com',
                'telefone' => '(11) 99999-9999',
            ],
            [
                'cnpj' => '98.765.432/0001-11',
                'nome' => 'Mariana Souza',
                'crc' => 'CRC-654321',
                'email' => 'mariana.souza@contabilidade.com',
                'telefone' => '(11) 98888-8888',
            ],
            [
                'cnpj' => '55.666.777/0001-22',
                'nome' => 'João Pereira',
                'crc' => 'CRC-111222',
                'email' => 'joao.pereira@contabilidade.com',
                'telefone' => '(21) 97777-7777',
            ],
        ];

        // Criar e salvar cada contador em uma variável específica
        $this->contador1 = EmpresaContador::create($contadores[0]);
        $this->contador2 = EmpresaContador::create($contadores[1]);
        $this->contador3 = EmpresaContador::create($contadores[2]);
    }
}
