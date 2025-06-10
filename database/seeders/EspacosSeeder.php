<?php

namespace Database\Seeders;

use App\Models\EmpresaContador;
use App\Models\Espaco;
use Illuminate\Database\Seeder;

class EspacosSeeder extends Seeder
{
    public $espaco1;
    public $espaco2;
    public $espaco3;
    public $espaco4;
    public $espaco5;

    public function run(): void
    {
        $espacos = [
            [
                'nome' => 'Salão Bubu Princes',
                'status' => 'disponivel',
                'valor' => 3000,
                'empresa_id' => 1
            ],
            [
                'nome' => 'Salão Lady Laura',
                'status' => 'disponivel',
                'valor' => 3700,
                'empresa_id' => 1
            ],
            [
                'nome' => 'Salão Rainha Sophia',
                'status' => 'disponivel',
                'valor' => 2500,
                'empresa_id' => 1
            ],
            [
                'nome' => 'Salão Charmosa Estrela',
                'status' => 'disponivel',
                'valor' => 4000,
                'empresa_id' => 1
            ],
            [
                'nome' => 'Salão Bella Vitória',
                'status' => 'disponivel',
                'valor' => 2800,
                'empresa_id' => 1
            ],
           
           
        ];

        // Criar e salvar cada contador em uma variável específica
        $this->espaco1 = Espaco::create($espacos[0]);
        $this->espaco2 = Espaco::create($espacos[1]);
        $this->espaco3 = Espaco::create($espacos[2]);
        $this->espaco3 = Espaco::create($espacos[2]);
        $this->espaco3 = Espaco::create($espacos[2]);
    }
}
