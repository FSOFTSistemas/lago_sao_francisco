<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $empresaId = session('empresa_id');

        if (!$empresaId) return;

        if (Schema::hasColumn($model->getTable(), 'empresa_id')) {
            $builder->where($model->getTable() . '.empresa_id', $empresaId);
        } elseif (method_exists($model, 'funcionario')) {
            $builder->whereHas('funcionario', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            });
        }
    }
}

