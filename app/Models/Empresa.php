<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco',
        'inscricao_estadual',
        'contador_id',
        'responsavel_tecnico_id'
    ];

    public function contador(): BelongsTo
    {
        return $this->belongsTo(EmpresaContador::class, 'contador_id');
    }

    public function responsavelTecnico(): BelongsTo
    {
        return $this->belongsTo(EmpresaRT::class, 'responsavel_tecnico_id');
    }

    public function preferencia(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmpresaPreferencia::class, 'empresa_id');
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    /**
     * Retorna o código numérico do estado (cUF) do padrão IBGE/SEFAZ.
     */
    public static function getCUF(?string $uf): string
    {
        $map = [
            'RO' => '11', 'AC' => '12', 'AM' => '13', 'RR' => '14', 'PA' => '15',
            'AP' => '16', 'TO' => '17', 'MA' => '21', 'PI' => '22', 'CE' => '23',
            'RN' => '24', 'PB' => '25', 'PE' => '26', 'AL' => '27', 'SE' => '28',
            'BA' => '29', 'MG' => '31', 'ES' => '32', 'RJ' => '33', 'SP' => '35',
            'PR' => '41', 'SC' => '42', 'RS' => '43', 'MS' => '50', 'MT' => '51',
            'GO' => '52', 'DF' => '53',
        ];

        return $map[strtoupper(trim((string)$uf))] ?? '29';
    }
}
