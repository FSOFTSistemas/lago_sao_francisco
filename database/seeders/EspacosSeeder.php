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
                'valor_semana' => 5000,
                'valor_fim' => 7000,
                'empresa_id' => 1,
                'capela' => false
            ],
            [
                'nome' => 'Salão Lady Laura',
                'valor_semana' => 8000,
                'valor_fim' => 10000,
                'empresa_id' => 1,
                'capela' => false
            ],
            [
                'nome' => 'Capela',
                'valor_semana' => 1200,
                'valor_fim' => 2200,
                'empresa_id' => 1,
                'capela' => true
            ],
            [
                'nome' => 'Gazebo Flutuante',
                'valor_semana' => 4000,
                'valor_fim' => 5700,
                'empresa_id' => 1,
                'capela' => false
            ],
           
           
        ];

        // Criar e salvar cada contador em uma variável específica
        $this->espaco1 = Espaco::create($espacos[0]);
        $this->espaco2 = Espaco::create($espacos[1]);
        $this->espaco3 = Espaco::create($espacos[2]);
        $this->espaco4 = Espaco::create($espacos[3]);
    }
}
