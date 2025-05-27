<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Produto;
use Livewire\Component;

class NFeNew extends Component
{ public $empresa = 'Minha Empresa LTDA';
    public $numero;
    public $serie;
    public $data_emissao;
    public $data_saida;
    public $tipo_nota = 'saida';
    public $finalidade;
    public $forma_pagamento;

    public $aba = 'itens';

    public $modalAberto = false;
    public $mostrarTributaria = false;

    public $modalProdutoAberto = false;
    public $buscaProduto = '';

    public $produtos = [
        ['nome' => 'Produto A'],
        ['nome' => 'Produto B'],
        ['nome' => 'Produto C'],
    ];

    public $novoItem = [
        'produto' => '',
        'quantidade' => 1,
        'valor_unitario' => 0,
        'cst' => '',
        'cfop' => '',
        'csosn' => '',
        'aliquota' => 0,
        'valor_icms' => 0,
        'base_calculo' => 0,
    ];

    public $itens = [];

    public function openModal()
    {
        $this->resetNovoItem();
        $this->modalAberto = true;
    }

    public function fecharModal()
    {
        $this->modalAberto = false;
        $this->mostrarTributaria = false;
    }

    public function salvarItem()
    {
        $subtotal = $this->novoItem['quantidade'] * $this->novoItem['valor_unitario'];
        $this->novoItem['total'] = $subtotal;

        $this->itens[] = $this->novoItem;

        $this->fecharModal();
    }

    public function resetNovoItem()
    {
        $this->novoItem = [
            'produto' => '',
            'quantidade' => 1,
            'valor_unitario' => 0,
            'cst' => '',
            'cfop' => '',
            'csosn' => '',
            'aliquota' => 0,
            'valor_icms' => 0,
            'base_calculo' => 0,
        ];
    }

    public function abrirModalProduto()
    {
        $this->modalProdutoAberto = true;
        $this->buscaProduto = '';
    }

    public function fecharModalProduto()
    {
        $this->modalProdutoAberto = false;
    }

    public function selecionarProduto($nomeProduto)
    {
        $this->novoItem['produto'] = $nomeProduto;
        $this->modalProdutoAberto = false;
    }

    public function render()
    {
        $produtosFiltrados = collect($this->produtos)
            ->filter(function ($produto) {
                return stripos($produto['nome'], $this->buscaProduto) !== false;
            })->toArray();

        return view('livewire.n-fe-new', [
            'produtos' => $produtosFiltrados,
        ]);
    }
}
