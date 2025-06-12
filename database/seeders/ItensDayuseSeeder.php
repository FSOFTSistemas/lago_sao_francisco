<?php

namespace Database\Seeders;

use App\Models\ItensDayUse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItensDayuseSeeder extends Seeder
{
   public $item1;
   public $item2;
   public $item3;
   public $item4;
   public $item5;
   public $item6;
   public $item7;
   public $item8;
   public $item9;
   public $item10;
    public function run(): void
    {
        $itens = [
            [
                'descricao' => 'Adulto',
                'valor' => '35',
                'passeio' => false
            ],
            [
                'descricao' => 'Meia Entrada',
                'valor' => '17.5',
                'passeio' => false
            ],
            [
                'descricao' => 'Circuito',
                'valor' => '50',
                'passeio' => false
            ],
            [
                'descricao' => 'Pedalinho',
                'valor' => '25',
                'passeio' => true
            ],
            [
                'descricao' => 'Tirolesa',
                'valor' => '25',
                'passeio' => true
            ],
            [
                'descricao' => 'Pônei',
                'valor' => '15',
                'passeio' => true
            ],
            [
                'descricao' => 'Cine',
                'valor' => '15',
                'passeio' => true
            ],
            [
                'descricao' => 'Ensaio',
                'valor' => '50',
                'passeio' => false
            ],
            [
                'descricao' => 'Outros',
                'valor' => '25',
                'passeio' => false
            ],
            [
                'descricao' => 'Excursão',
                'valor' => '50',
                'passeio' => false
            ],
        ];

        $this->item1 = ItensDayUse::create($itens[0]);
        $this->item2 = ItensDayUse::create($itens[1]);
        $this->item3 = ItensDayUse::create($itens[2]);
        $this->item4 = ItensDayUse::create($itens[3]);
        $this->item5 = ItensDayUse::create($itens[4]);
        $this->item6 = ItensDayUse::create($itens[5]);
        $this->item7 = ItensDayUse::create($itens[6]);
        $this->item8 = ItensDayUse::create($itens[7]);
        $this->item9 = ItensDayUse::create($itens[8]);
        $this->item10 = ItensDayUse::create($itens[9]);
    }
}
