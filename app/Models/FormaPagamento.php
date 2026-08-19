<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'descricao',
    ];

    public static function slugMovimento(?string $descricao): string
    {
        return strtolower(str_replace([' ', '_'], '-', trim((string) $descricao)));
    }

    public static function descricaoMovimento(string $prefixo, ?string $descricao): string
    {
        return $prefixo.'-'.self::slugMovimento($descricao);
    }

    public function movimentoSlug(): string
    {
        return self::slugMovimento($this->slug ?? $this->descricao ?? '');
    }

    public function movimentoDescricao(string $prefixo): string
    {
        return self::descricaoMovimento($prefixo, $this->slug ?? $this->descricao ?? '');
    }

    public function venda()
    {
        return $this->hasMany(Venda::class, 'forma_pagamento_id');
    }

    public function recebimentosExcursao(): HasMany
    {
        return $this->hasMany(RecebimentoExcursao::class);
    }
}
