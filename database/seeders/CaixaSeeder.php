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
        $caixas = [
            [
            'descricao' => 'caixa Lago',
            'valor_inicial' => 0,
            'valor_final' => 0,
            'data_abertura' => Carbon::now(),
            'data_fechamento' => null,
            'status' => 'aberto',
            'observacoes' => null,
            'empresa_id' => 1,
            'usuario_abertura_id' => 1,
            'usuario_fechamento_id' => null,
        ],
        [
            'descricao' => 'caixa Restaurante',
            'valor_inicial' => 0,
            'valor_final' => 0,
            'data_abertura' => Carbon::now(),
            'data_fechamento' => null,
            'status' => 'aberto',
            'observacoes' => null,
            'empresa_id' => 2,
            'usuario_abertura_id' => 1,
            'usuario_fechamento_id' => null,
        ],
        [
            'descricao' => 'caixa Hotel',
            'valor_inicial' => 0,
            'valor_final' => 0,
            'data_abertura' => Carbon::now(),
            'data_fechamento' => null,
            'observacoes' => null,
            'status' => 'aberto',
            'empresa_id' => 3,
            'usuario_abertura_id' => 1,
            'usuario_fechamento_id' => null,
        ],
    ];
    $this->caixaLago = Caixa::create($caixas[0]);
    $this->caixaRestaurante = Caixa::create($caixas[1]);
    $this->caixaHotel = Caixa::create($caixas[2]);
    }
}
