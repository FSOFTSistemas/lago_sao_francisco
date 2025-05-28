<?php

namespace Database\Seeders;
use App\Models\Empresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public $empresaLago;
    public $empresaRestaurante;
    public $empresaHotel;


    public $contador1;
    public $contador2;
    public $contador3;

    public $rt;
    
    
    public function run(): void
    {
        $contador1 = $this->contador1;
        $contador2 = $this->contador2;
        $contador3 = $this->contador3;

        $rt = $this->rt;

        $empresaLago = Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Lago São Francisco',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
            'contador_id'=> $contador1,
            'responsavel_tecnico_id' => $rt->id
        ]);
        
        $empresaRestaurante = Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Restaurante Dom Dina',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
            'contador_id'=> $contador2,
            'responsavel_tecnico_id' => $rt->id
        ]);
        
        $empresaHotel = Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Hotel Estação Chico',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
            'contador_id'=> $contador3,
            'responsavel_tecnico_id' => $rt->id
        ]);

        $this->empresaHotel = $empresaHotel;
        $this->empresaRestaurante = $empresaRestaurante;
        $this->empresaLago = $empresaLago;
    }
}
