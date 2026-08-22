<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ActiveCompanyResolver;
use App\Support\CompanyContext;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveCompany
{
    public function __construct(
        private readonly ActiveCompanyResolver $resolver,
        private readonly CompanyContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $company = $this->resolver->resolve(
            $user,
            $request->header('X-Empresa-Id'),
        );

        $this->context->set($company);
        $request->attributes->set('empresa', $company);
        $request->attributes->set('empresa_id', $company->getKey());

        return $next($request);
    }
}
