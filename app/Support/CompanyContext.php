<?php

namespace App\Support;

use App\Models\Empresa;
use LogicException;

class CompanyContext
{
    private ?Empresa $company = null;

    public function set(Empresa $company): void
    {
        if ($this->company !== null && $this->company->getKey() !== $company->getKey()) {
            throw new LogicException('A empresa ativa não pode ser alterada durante a requisição.');
        }

        $this->company = $company;
    }

    public function company(): Empresa
    {
        if ($this->company === null) {
            throw new LogicException('A empresa ativa ainda não foi resolvida.');
        }

        return $this->company;
    }

    public function id(): int
    {
        return (int) $this->company()->getKey();
    }

    public function resolved(): bool
    {
        return $this->company !== null;
    }
}
