<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaPreferencia;

class PreferenciaSeeder extends Seeder
{
    public $empresa1Id;
    public $empresa2Id;
    public $empresa3Id;
    public function run(): void
    {
        $empresa1Id = $this->empresa1Id;
        $empresa2Id = $this->empresa2Id;
        $empresa3Id = $this->empresa3Id;
        // Exemplo para 3 empresas existentes, ajuste os IDs conforme seu banco
        $preferencias = [
            [
                'empresa_id' => $empresa1Id,
                'certificado_digital' => null,
                'numero_ultima_nota' => 123,
                'serie' => 'A1',
                'cfop_padrao' => '5101',
                'regime_tributario' => 'Simples Nacional',
            ],
            [
                'empresa_id' => $empresa2Id,
                'certificado_digital' => null,
                'numero_ultima_nota' => 456,
                'serie' => 'B2',
                'cfop_padrao' => '6102',
                'regime_tributario' => 'Lucro Presumido',
            ],
            [
                'empresa_id' => $empresa3Id,
                'certificado_digital' => null,
                'numero_ultima_nota' => 789,
                'serie' => 'C3',
                'cfop_padrao' => '5102',
                'regime_tributario' => 'Lucro Real',
            ],
        ];

        foreach ($preferencias as $pref) {
            EmpresaPreferencia::create($pref);
        }
    }
}
