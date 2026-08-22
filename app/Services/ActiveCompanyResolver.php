<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ActiveCompanyResolver
{
    public function resolve(User $user, mixed $requestedCompanyId = null): Empresa
    {
        return Empresa::query()->findOrFail(
            $this->resolveCompanyId($user, $requestedCompanyId)
        );
    }

    public function resolveCompanyId(User $user, mixed $requestedCompanyId = null): int
    {
        $userCompanyId = $this->normalizeCompanyId($user->empresa_id, 'usuário');

        if ($requestedCompanyId === null || $requestedCompanyId === '') {
            return $userCompanyId;
        }

        $requestedCompanyId = $this->normalizeCompanyId($requestedCompanyId, 'cabeçalho X-Empresa-Id');

        if ($requestedCompanyId !== $userCompanyId && ! $user->hasRole('Master')) {
            throw new AuthorizationException('Você não possui acesso à empresa informada.');
        }

        return $requestedCompanyId;
    }

    private function normalizeCompanyId(mixed $companyId, string $source): int
    {
        $isIntegerString = is_string($companyId)
            && $companyId !== ''
            && ctype_digit($companyId);

        if ((! is_int($companyId) && ! $isIntegerString) || (int) $companyId < 1) {
            throw ValidationException::withMessages([
                'empresa_id' => "O identificador de empresa do {$source} é inválido.",
            ]);
        }

        return (int) $companyId;
    }
}
