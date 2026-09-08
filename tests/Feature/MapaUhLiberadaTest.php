<?php

namespace Tests\Feature;

use App\Http\Controllers\MapaController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MapaUhLiberadaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach (['funcionarios', 'users', 'categorias', 'hospedes', 'motorhomes'] as $nome) {
            Schema::create($nome, function (Blueprint $table) {
                $table->id();
                $table->string('nome')->nullable();
                $table->string('name')->nullable();
            });
        }

        Schema::create('quartos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('categoria_id')->nullable();
            $table->integer('posicao')->default(1);
            $table->boolean('status')->default(true);
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->integer('quarto_id');
            $table->date('data_checkin');
            $table->date('data_checkout');
            $table->string('situacao');
        });

        Schema::create('reserva_pets', function (Blueprint $table) {
            $table->id();
            $table->integer('reserva_id');
        });

        DB::table('quartos')->insert(['id' => 1, 'nome' => 'UH 01']);
        DB::table('reservas')->insert([
            'id' => 1,
            'quarto_id' => 1,
            'data_checkin' => '2026-09-01',
            'data_checkout' => '2026-09-05',
            'situacao' => 'finalizada',
        ]);
    }

    public function test_liberacao_preserva_periodo_completo_no_mapa(): void
    {
        $antes = $this->dadosMapa('2026-09-01')['quartos'][0]['reservas'][0];

        DB::table('reservas')->where('id', 1)->update(['situacao' => 'uh_liberada']);

        $depois = $this->dadosMapa('2026-09-01')['quartos'][0]['reservas'][0];

        $this->assertSame('uh_liberada', $depois['situacao']);
        $this->assertSame('2026-09-01', $depois['data_mapa_checkin']);
        $this->assertSame('2026-09-05', $depois['data_mapa_checkout']);
        foreach (['data_checkin', 'data_checkout', 'data_mapa_checkin', 'data_mapa_checkout'] as $campo) {
            $this->assertSame($antes[$campo], $depois[$campo]);
        }
    }

    public function test_liberacao_nao_cria_dia_extra_apos_fim_da_estadia(): void
    {
        DB::table('reservas')->where('id', 1)->update(['situacao' => 'uh_liberada']);

        $dados = $this->dadosMapa('2026-09-05');

        $this->assertSame([], $dados['quartos'][0]['reservas']);
    }

    private function dadosMapa(string $inicio): array
    {
        $response = (new MapaController)->getDadosMapa(Request::create('/mapa/dados', 'GET', [
            'data_inicio' => $inicio,
            'data_fim' => '2026-09-10',
        ]));

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());

        return $response->getData(true);
    }
}
