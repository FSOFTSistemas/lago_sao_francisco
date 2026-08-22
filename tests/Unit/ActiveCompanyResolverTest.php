<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ActiveCompanyResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ActiveCompanyResolverTest extends TestCase
{
    private ActiveCompanyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ActiveCompanyResolver;
    }

    public function test_usa_a_empresa_do_usuario_quando_o_cabecalho_nao_e_enviado(): void
    {
        $user = $this->user(7, false);

        $this->assertSame(7, $this->resolver->resolveCompanyId($user));
    }

    public function test_financeiro_pode_repetir_a_propria_empresa_no_cabecalho(): void
    {
        $user = $this->user(7, false);

        $this->assertSame(7, $this->resolver->resolveCompanyId($user, '7'));
    }

    public function test_financeiro_nao_pode_selecionar_outra_empresa(): void
    {
        $user = $this->user(7, false);

        $this->expectException(AuthorizationException::class);

        $this->resolver->resolveCompanyId($user, '8');
    }

    public function test_master_pode_selecionar_outra_empresa(): void
    {
        $user = $this->user(7, true);

        $this->assertSame(8, $this->resolver->resolveCompanyId($user, '8'));
    }

    #[DataProvider('invalidCompanyIds')]
    public function test_rejeita_identificador_de_empresa_invalido(mixed $companyId): void
    {
        $user = $this->user(7, true);

        $this->expectException(ValidationException::class);

        $this->resolver->resolveCompanyId($user, $companyId);
    }

    public static function invalidCompanyIds(): array
    {
        return [
            'zero' => [0],
            'negativo' => [-1],
            'decimal' => ['1.5'],
            'texto' => ['empresa'],
            'array' => [[1]],
        ];
    }

    private function user(int $companyId, bool $master): User&MockObject
    {
        $user = $this->getMockBuilder(User::class)
            ->onlyMethods(['hasRole'])
            ->getMock();
        $user->empresa_id = $companyId;
        $user->method('hasRole')->with('Master')->willReturn($master);

        return $user;
    }
}
