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

    // === Relacionamentos Many-to-Many ===

    public function adicionais()
    {
        return $this->belongsToMany(Adicional::class, 'adicionais_aluguel');
    }

    // === Relacionamentos do Buffet ===

    /**
     * Relacionamento com as escolhas do buffet
     */
    public function buffetEscolhas()
    {
        return $this->hasMany(BuffetEscolha::class);
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
    
    // === Escopos globais ===

    protected static function booted()
    {
        static::addGlobalScope(new EmpresaScope);
    }
}

