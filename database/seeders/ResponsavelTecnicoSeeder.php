<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaRT; // Ajuste conforme seu model

class ResponsavelTecnicoSeeder extends Seeder
{
    public $RT;
    public function run(): void
    {
        $rt = EmpresaRT::create([
            'nome' => 'Fsoft Sistemas LTDA',
            'cnpj' => '60.177.690/0001-80',
            'telefone' => "8781753993",   // Insira telefone se desejar
            'email' => "lucianoalves413@gmail.com",      // Insira email se desejar
        ]);

        $this->RT = $rt;
    }
}
