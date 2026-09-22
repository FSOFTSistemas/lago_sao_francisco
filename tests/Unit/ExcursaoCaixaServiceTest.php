<?php

namespace Tests\Unit;

use App\Models\Caixa;
use App\Models\User;
use App\Services\CaixaService;
use App\Services\ExcursaoCaixaService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExcursaoCaixaServiceTest extends TestCase
{
    private ExcursaoCaixaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('data_abertura');
            $table->string('status');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
        });

        $this->service = new ExcursaoCaixaService($this->createMock(CaixaService::class));
    }

    public function test_lanca_excecao_quando_usuario_nao_autenticado_ou_sem_empresa(): void
    {
        Auth::logout();
        session()->forget('empresa_id');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Não foi possível identificar a empresa do usuário para realizar o agendamento.');

        $this->service->caixaAbertoDoUsuario();
    }

    public function test_lanca_excecao_quando_usuario_nao_tem_caixa_aberto_hoje(): void
    {
        $usuario = new User([
            'id' => 999,
            'name' => 'Operador Teste',
            'email' => 'operador@teste.com',
            'empresa_id' => 1,
        ]);
        $usuario->id = 999;

        Auth::setUser($usuario);
        session(['empresa_id' => 1]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Abra o seu caixa do dia antes de agendar uma excursão.');

        $this->service->caixaAbertoDoUsuario();
    }

    public function test_retorna_caixa_quando_usuario_tem_caixa_aberto_hoje(): void
    {
        $usuario = new User([
            'id' => 888,
            'name' => 'Operador Teste',
            'email' => 'operador@teste.com',
            'empresa_id' => 1,
        ]);
        $usuario->id = 888;

        Auth::setUser($usuario);
        session(['empresa_id' => 1]);

        Caixa::create([
            'data_abertura' => now(),
            'status' => 'aberto',
            'empresa_id' => 1,
            'usuario_id' => 888,
        ]);

        $caixa = $this->service->caixaAbertoDoUsuario();

        $this->assertInstanceOf(Caixa::class, $caixa);
        $this->assertSame(888, (int) $caixa->usuario_id);
        $this->assertSame('aberto', $caixa->status);
    }
}
