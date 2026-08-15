<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoDeConta extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'descricao',
        'tipo',
        'plano_de_conta_pai',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function planoPai()
    {
        return $this->belongsTo($this, 'plano_de_conta_pai');
    }

    public function filhos()
    {
        return $this->hasMany($this, 'plano_de_conta_pai');
    }

    public static function idPorDescricao(string|array $descricoes, ?int $empresaId = null, ?string $tipo = null): ?int
    {
        $descricoes = is_array($descricoes) ? $descricoes : [$descricoes];

        foreach ($descricoes as $descricao) {
            if ($empresaId) {
                $id = static::query()
                    ->where('descricao', $descricao)
                    ->when($tipo, fn ($query) => $query->where('tipo', $tipo))
                    ->where('empresa_id', $empresaId)
                    ->value('id');

                if ($id) {
                    return $id;
                }
            }

            $id = static::query()
                ->where('descricao', $descricao)
                ->when($tipo, fn ($query) => $query->where('tipo', $tipo))
                ->whereNull('empresa_id')
                ->value('id');

            if ($id) {
                return $id;
            }

            $id = static::query()
                ->where('descricao', $descricao)
                ->when($tipo, fn ($query) => $query->where('tipo', $tipo))
                ->value('id');

            if ($id) {
                return $id;
            }
        }

        return null;
    }
}
