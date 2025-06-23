<?php

namespace App\Models;

use App\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluguel extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_inicio',
        'data_fim',
        'observacoes',
        'subtotal',
        'total',
        'acrescimo',
        'desconto',
        'parcelas',
        'vencimento',
        'contrato',
        'status',
        'espaco_id',
        'cliente_id',
        'empresa_id',
        'forma_pagamento_id',
        'numero_pessoas_buffet',
        'cardapio_id'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    // === Relacionamentos diretos ===

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    public function espaco()
    {
        return $this->belongsTo(Espaco::class);
    }

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class);
    }

    // === Relacionamentos do Buffet ===

    /**
     * Relacionamento com as escolhas do buffet
     */
    public function buffetEscolhas()
    {
        return $this->hasMany(BuffetEscolha::class);
    }

    // === Relacionamentos do Adicional ===

    public function adicionaisAluguel()
    {
        return $this->hasMany(AdicionalAluguel::class);
    }

      // === Relacionamentos de Pagamento ===

    /**
     * Relacionamento com os pagamentos do aluguel
     */
    public function pagamentos()
    {
        return $this->hasMany(AluguelPagamento::class);
    }

    // === Métodos auxiliares para o Buffet ===

    /**
     * Recupera as escolhas de categorias do buffet agrupadas por categoria
     */
    public function getEscolhasCategorias()
    {
        return $this->buffetEscolhas()
                    ->where('tipo', 'categoria_item')
                    ->with(['categoria', 'item'])
                    ->get()
                    ->groupBy('categoria_id');
    }

    /**
     * Recupera a opção de refeição escolhida
     */
    public function getEscolhaOpcaoRefeicao()
    {
        return $this->buffetEscolhas()
                    ->where('tipo', 'opcao_refeicao')
                    ->with('opcaoRefeicao')
                    ->first();
    }

    /**
     * Calcula o total do buffet baseado na opção escolhida e número de pessoas
     */
    public function calcularTotalBuffet()
    {
        $opcaoRefeicao = $this->getEscolhaOpcaoRefeicao();
        
        if ($opcaoRefeicao && $this->numero_pessoas_buffet) {
            $precoPorPessoa = $opcaoRefeicao->opcaoRefeicao->preco_por_pessoa;
            return $this->numero_pessoas_buffet * $precoPorPessoa;
        }

        return 0;
    }

    /**
     * Verifica se o aluguel tem buffet ativo
     */
    public function temBuffet()
    {
        return !is_null($this->cardapio_id) && !is_null($this->numero_pessoas_buffet);
    }

    /**
     * Recupera todas as escolhas do buffet formatadas para exibição
     */
    public function getBuffetEscolhasFormatadas()
    {
        $escolhas = [
            'categorias' => [],
            'opcao_refeicao' => null,
            'total' => 0
        ];

        // Categorias e itens
        $categorias = $this->getEscolhasCategorias();
        foreach ($categorias as $categoriaId => $itens) {
            $categoria = $itens->first()->categoria;
            $escolhas['categorias'][] = [
                'nome' => $categoria->nome,
                'itens' => $itens->pluck('item.nome')->toArray()
            ];
        }

        // Opção de refeição
        $opcaoRefeicao = $this->getEscolhaOpcaoRefeicao();
        if ($opcaoRefeicao) {
            $escolhas['opcao_refeicao'] = [
                'nome' => $opcaoRefeicao->opcaoRefeicao->nome,
                'preco_por_pessoa' => $opcaoRefeicao->opcaoRefeicao->preco_por_pessoa
            ];
        }

        // Total
        $escolhas['total'] = $this->calcularTotalBuffet();

        return $escolhas;
    }

    // === Métodos auxiliares para Pagamento ===

    /**
     * Calcula o total pago para este aluguel
     */
    public function getTotalPago()
    {
        return $this->pagamentos()->sum('valor');
    }

    /**
     * Calcula o valor restante a ser pago
     */
    public function getValorRestante()
    {
        return $this->total - $this->getTotalPago();
    }

    /**
     * Verifica se o aluguel está totalmente pago
     */
    public function estaPago()
    {
        return $this->getValorRestante() <= 0;
    }

    /**
     * Recupera os pagamentos formatados para exibição
     */
    public function getPagamentosFormatados()
    {
        return $this->pagamentos()
                    ->with('formaPagamento')
                    ->get()
                    ->map(function ($pagamento) {
                        return [
                            'id' => $pagamento->id,
                            'forma_pagamento' => $pagamento->formaPagamento->nome,
                            'valor' => $pagamento->valor,
                            'data_pagamento' => $pagamento->created_at->format('d/m/Y H:i')
                        ];
                    });
    }
    
    // === Escopos globais ===

    protected static function booted()
    {
        static::addGlobalScope(new EmpresaScope);
    }
}

