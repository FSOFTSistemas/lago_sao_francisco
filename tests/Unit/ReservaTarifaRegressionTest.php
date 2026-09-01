<?php

namespace Tests\Unit;

use App\Http\Controllers\ReservaController;
use App\Models\Categoria;
use App\Models\Quarto;
use App\Models\Reserva;
use App\Models\Tarifa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class ReservaTarifaRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->integer('ocupantes');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('quartos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('categoria_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('categoria_id');
            $table->boolean('ativo')->default(true);
            $table->boolean('alta_temporada')->default(false);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
                $table->decimal($dia, 10, 2)->default(0);
            }
            $table->integer('padrao_adultos')->default(2);
            $table->integer('padrao_criancas')->default(0);
            $table->decimal('adicional_adulto', 10, 2)->default(0);
            $table->decimal('adicional_crianca', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('temporadas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->timestamps();
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarto_id');
            $table->unsignedBigInteger('hospede_id')->nullable();
            $table->date('data_checkin');
            $table->date('data_checkout');
            $table->decimal('valor_diaria', 10, 2);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('situacao');
            $table->integer('n_adultos');
            $table->integer('n_criancas')->default(0);
            $table->integer('n_criancas_nao_pagantes')->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('reserva_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id');
            $table->string('tamanho');
            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->timestamps();
        });

        Schema::create('preferencias_hotels', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor_pet_pequeno', 10, 2)->default(0);
            $table->decimal('valor_pet_medio', 10, 2)->default(0);
            $table->decimal('valor_pet_grande', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('logs_reserva', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('tipo');
            $table->text('descricao');
            $table->json('dados_antigos')->nullable();
            $table->json('dados_novos')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();
        parent::tearDown();
    }

    public function test_edicao_recalcula_diaria_quando_quantidade_de_adultos_muda(): void
    {
        [$quarto] = $this->criarQuartoETarifa(100, true, 50);
        $reserva = Reserva::create([
            'quarto_id' => $quarto->id,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-01',
            'valor_diaria' => 100,
            'valor_total' => 100,
            'situacao' => 'reserva',
            'n_adultos' => 2,
            'n_criancas' => 0,
        ]);

        $request = Request::create('/reserva/'.$reserva->id, 'PUT', [
            'quarto_id' => $quarto->id,
            'hospede_id' => null,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-01',
            'valor_diaria' => '100,00',
            'situacao' => 'reserva',
            'n_adultos' => 3,
            'n_criancas' => 0,
            'n_criancas_nao_pagantes' => 0,
        ]);

        (new ReservaController)->update($request, $reserva);

        $this->assertSame(150.0, (float) $reserva->fresh()->valor_diaria);
    }

    public function test_calculo_ignora_tarifa_inativa(): void
    {
        [$quarto] = $this->criarQuartoETarifa(100, false);
        $this->criarTarifa($quarto->categoria_id, 200, true);

        $resultado = $this->calcularTarifa($quarto, '2026-08-31', '2026-09-01');

        $this->assertSame('200.00', $resultado['valor_diaria']);
    }

    public function test_checkin_e_checkout_iguais_cobram_uma_diaria(): void
    {
        [$quarto] = $this->criarQuartoETarifa(180);

        $resultado = $this->calcularTarifa($quarto, '2026-08-31', '2026-08-31');

        $this->assertSame('180.00', $resultado['valor_diaria']);
    }

    public function test_edicao_soma_tarifa_de_pet_por_quantidade_e_diarias(): void
    {
        [$quarto] = $this->criarQuartoETarifa(100);
        DB::table('preferencias_hotels')->insert([
            'valor_pet_pequeno' => 25,
            'valor_pet_medio' => 40,
            'valor_pet_grande' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $reserva = Reserva::create([
            'quarto_id' => $quarto->id,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-03',
            'valor_diaria' => 100,
            'valor_total' => 300,
            'situacao' => 'reserva',
            'n_adultos' => 2,
            'n_criancas' => 0,
        ]);

        $request = Request::create('/reserva/'.$reserva->id, 'PUT', [
            'quarto_id' => $quarto->id,
            'hospede_id' => null,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-03',
            'valor_diaria' => '100,00',
            'situacao' => 'reserva',
            'n_adultos' => 2,
            'n_criancas' => 0,
            'n_criancas_nao_pagantes' => 0,
            'qtd_pet_pequeno' => 2,
        ]);

        (new ReservaController)->update($request, $reserva);

        $reserva->refresh();
        $this->assertSame(450.0, (float) $reserva->valor_total);
        $this->assertDatabaseHas('reserva_pets', [
            'reserva_id' => $reserva->id,
            'tamanho' => 'pequeno',
            'quantidade' => 2,
            'valor_unitario' => 25,
        ]);
    }

    public function test_edicao_recalcula_tarifa_de_pet_quando_periodo_muda(): void
    {
        [$quarto] = $this->criarQuartoETarifa(100);
        DB::table('preferencias_hotels')->insert([
            'valor_pet_pequeno' => 25,
            'valor_pet_medio' => 40,
            'valor_pet_grande' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $reserva = Reserva::create([
            'quarto_id' => $quarto->id,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-01',
            'valor_diaria' => 100,
            'valor_total' => 125,
            'situacao' => 'reserva',
            'n_adultos' => 2,
            'n_criancas' => 0,
        ]);

        $request = Request::create('/reserva/'.$reserva->id, 'PUT', [
            'quarto_id' => $quarto->id,
            'hospede_id' => null,
            'data_checkin' => '2026-08-31',
            'data_checkout' => '2026-09-02',
            'valor_diaria' => '100,00',
            'situacao' => 'reserva',
            'n_adultos' => 2,
            'n_criancas' => 0,
            'n_criancas_nao_pagantes' => 0,
            'qtd_pet_pequeno' => 1,
        ]);

        (new ReservaController)->update($request, $reserva);

        $this->assertSame(250.0, (float) $reserva->fresh()->valor_total);
    }

    private function criarQuartoETarifa(float $valor, bool $ativo = true, float $adicionalAdulto = 0): array
    {
        $categoria = Categoria::create(['titulo' => 'Standard', 'ocupantes' => 4, 'status' => true]);
        $quarto = Quarto::create(['nome' => 'UH 01', 'categoria_id' => $categoria->id, 'status' => true]);
        $tarifa = $this->criarTarifa($categoria->id, $valor, $ativo, $adicionalAdulto);

        return [$quarto, $tarifa];
    }

    private function criarTarifa(int $categoriaId, float $valor, bool $ativo, float $adicionalAdulto = 0): Tarifa
    {
        return Tarifa::create([
            'nome' => 'Tarifa '.$valor,
            'categoria_id' => $categoriaId,
            'ativo' => $ativo,
            'alta_temporada' => false,
            'seg' => $valor,
            'ter' => $valor,
            'qua' => $valor,
            'qui' => $valor,
            'sex' => $valor,
            'sab' => $valor,
            'dom' => $valor,
            'padrao_adultos' => 2,
            'padrao_criancas' => 0,
            'adicional_adulto' => $adicionalAdulto,
            'adicional_crianca' => 0,
        ]);
    }

    private function calcularTarifa(Quarto $quarto, string $checkin, string $checkout): array
    {
        $metodo = new ReflectionMethod(ReservaController::class, 'calcularTarifaAutomatica');

        return $metodo->invoke(new ReservaController, $quarto->id, $checkin, $checkout, 2, 0);
    }
}
