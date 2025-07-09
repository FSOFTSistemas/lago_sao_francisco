<?php

namespace Database\Seeders;

use App\Models\Adicional;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MobiliaSeeder extends Seeder
{
    public $mobilia1;
    public $mobilia2;
    public $mobilia3;
    public $mobilia4;
    public $mobilia5;
    public function run(): void
    {
        $mobilias = [
            [
                'descricao' => 'Cadeiras Princess em ferro',
                'valor' => 6,
                'quantidade' => 140
            ],
            [
                'descricao' => 'Cadeiras Tiffanys em ferro',
                'valor' => 6,
                'quantidade' => 200
            ],
            [
                'descricao' => 'Cadeiras Alices em pvc',
                'valor' => 4,
                'quantidade' => 150
            ],
            [
                'descricao' => 'Mesas com tampo redondo para 6 pessoas',
                'valor' => 10,
                'quantidade' => 30
            ],
            [
                'descricao' => 'Mesas com tampo redondo para 6 pessoas',
                'valor' => 12,
                'quantidade' => 40
            ],
        ];

        $this->mobilia1 = Adicional::create($mobilias[0]);
        $this->mobilia2 = Adicional::create($mobilias[1]);
        $this->mobilia3 = Adicional::create($mobilias[2]);
        $this->mobilia4 = Adicional::create($mobilias[3]);
        $this->mobilia5 = Adicional::create($mobilias[4]);
    }
}
