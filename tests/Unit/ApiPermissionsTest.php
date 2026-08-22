<?php

namespace Tests\Unit;

use App\Support\ApiPermissions;
use PHPUnit\Framework\TestCase;

class ApiPermissionsTest extends TestCase
{
    public function test_permissoes_sao_unicas_e_exclusivas_do_aplicativo(): void
    {
        $permissions = ApiPermissions::all();

        $this->assertSame($permissions, array_values(array_unique($permissions)));

        foreach ($permissions as $permission) {
            $this->assertStringStartsWith('app.', $permission);
        }
    }

    public function test_dona_recebe_somente_permissoes_de_consulta(): void
    {
        $ownerPermissions = ApiPermissions::ownerReadOnly();

        $this->assertNotEmpty($ownerPermissions);
        $this->assertSame([], array_intersect($ownerPermissions, ApiPermissions::financialWrite()));

        foreach ($ownerPermissions as $permission) {
            $this->assertStringStartsWith('app.visualizar.', $permission);
        }
    }

    public function test_financeiro_recebe_consulta_e_operacao(): void
    {
        $this->assertSame(
            ApiPermissions::all(),
            ApiPermissions::financial(),
        );
        $this->assertSame(
            ApiPermissions::ownerReadOnly(),
            array_values(array_intersect(ApiPermissions::financial(), ApiPermissions::ownerReadOnly())),
        );
        $this->assertSame(
            ApiPermissions::financialWrite(),
            array_values(array_intersect(ApiPermissions::financial(), ApiPermissions::financialWrite())),
        );
    }
}
