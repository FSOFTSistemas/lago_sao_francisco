<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'descricao',
        'exige_comprovante',
    ];

    protected $casts = [
        'exige_comprovante' => 'boolean',
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

    public function movimentoSlugs(): array
    {
        $descricao = (string) ($this->slug ?? $this->descricao ?? '');
        $slug = $this->movimentoSlug();
        $slugNormalizado = Str::of($descricao)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();

        $aliases = [$slug, $slugNormalizado];

        if (str_contains($slugNormalizado, 'link') && str_contains($slugNormalizado, 'pagamento')) {
            $aliases[] = 'link-de-pagamento';
        }

        if (str_contains($slugNormalizado, 'maquineta')) {
            $aliases[] = 'maquineta-de-cartao';
            $aliases[] = 'pix-maquineta';
        }

        if ($slugNormalizado === 'pix') {
            $aliases[] = 'pix';
        }

        return array_values(array_unique(array_filter($aliases)));
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
