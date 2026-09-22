<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaPreferencia extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'certificado_digital',
        'senha_certificado',
        'ambiente_dfe',
        'ult_nsu',
        'max_nsu',
        'data_ultima_consulta_dfe',
        'cstat_ultima_consulta_dfe',
        'motivo_ultima_consulta_dfe',
        'numero_ultima_nota',
        'serie',
        'cfop_padrao',
        'regime_tributario',
        'empresa_id',
    ];

    protected $casts = [
        'data_ultima_consulta_dfe' => 'datetime',
        'ambiente_dfe' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Retorna os metadados do certificado digital (titular, validade, dias restantes).
     */
    public function getCertificadoInfo(): ?array
    {
        if (empty($this->certificado_digital)) {
            return null;
        }

        $caminhos = [
            storage_path('app/' . $this->certificado_digital),
            storage_path('app/public/' . $this->certificado_digital),
            storage_path('app/public/certificados/' . $this->certificado_digital),
            storage_path('app/certificados/' . $this->certificado_digital),
            public_path('certificados/' . $this->certificado_digital),
            $this->certificado_digital,
        ];

        $conteudo = null;
        foreach ($caminhos as $c) {
            if (\Illuminate\Support\Facades\File::exists($c) && \Illuminate\Support\Facades\File::isFile($c)) {
                $conteudo = file_get_contents($c);
                break;
            }
        }

        if (!$conteudo) {
            return null;
        }

        $senha = '';
        if (!empty($this->senha_certificado)) {
            try {
                $senha = \Illuminate\Support\Facades\Crypt::decryptString($this->senha_certificado);
            } catch (\Throwable $e) {
                $senha = $this->senha_certificado;
            }
        }

        try {
            $cert = \NFePHP\Common\Certificate::readPfx($conteudo, $senha);
            $validoAte = $cert->getValidTo();
            $diasRestantes = (int) ceil(now()->diffInSeconds($validoAte, false) / 86400);

            return [
                'titular'        => $cert->getCompanyName(),
                'cnpj'           => $cert->getCnpj() ?: $cert->getCpf(),
                'valido_de'      => $cert->getValidFrom(),
                'valido_ate'     => $validoAte,
                'expirado'       => $cert->isExpired(),
                'dias_restantes' => $diasRestantes,
            ];
        } catch (\Throwable $e) {
            return [
                'erro' => $e->getMessage(),
            ];
        }
    }
}
