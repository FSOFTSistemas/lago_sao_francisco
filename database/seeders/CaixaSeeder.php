<?php

namespace Database\Seeders;

use App\Models\Caixa;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaixaSeeder extends Seeder
{
    public $caixaLago;
    public $caixaHotel;
    public $caixaRestaurante;
    public function run(): void
    {
        $caixa = [
            'descricao' => 'caixa Master',
            'valor_inicial' => 0,
            'valor_final' => 0,
            'data_abertura' => Carbon::now(),
            'data_fechamento' => null,
            'status' => 'aberto',
            'observacoes' => null,
            'empresa_id' => 1,
            'usuario_abertura_id' => 1,
            'usuario_fechamento_id' => null,
            'usuario_id' => 1
    ];
    $this->caixaLago = Caixa::create($caixa);
   
    }
}
