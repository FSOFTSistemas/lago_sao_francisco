<?php

namespace App\Livewire;

use App\Http\Controllers\NotaFiscalController;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NFeNew extends Component
{

    public $empresa = 'FSOFT SISTEMAS';
    public $numero;
    public $serie;
    public $data_emissao;
    public $data_saida;
    public $tipo_nota = 'saida';
    public $finalidade;
    public $forma_pagamento;
    public $cliente = ['id' => null, 'nome' => 'Consumidor'];
    public $modalClienteAberto = false;
    public $buscaCliente = '';
    public $clientes = [];

    // Propriedades para campos dinâmicos da aba de faturamento
    public $forma_pagamento_detalhada = '';
    public $quantidade_parcelas;
    public $bandeira_cartao;
    public $data_vencimento;
    
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
        'subtotal' => 0,
        'total' => 0,
        'cst' => '',
        'cfop' => '',
        'csosn' => '',
        'aliquota' => 0,
        'valor_icms' => 0,
        'base_calculo' => 0,
    ];

    public $itens = [];

    public function mount()
    {
        $empresaID = Auth::user()->empresa_id;
        $this->empresa = Empresa::find($empresaID)->razao_social;
        $this->produtos = Produto::where('empresa_id', $empresaID)->get();
        $this->data_emissao = now()->toDateString();
        $this->data_saida = now()->toDateString();
        $this->serie = 1;
        $this->numero = 1;
    }

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
        $this->atualizarTotaisItem();
        $this->itens[] = $this->novoItem;
        $this->fecharModal();
    }

    public function resetNovoItem()
    {
        $this->novoItem = [
            'produto' => '',
            'quantidade' => 1,
            'valor_unitario' => 0,
            'subtotal' => 0,
            'total' => 0,
            'cst' => '',
            'cfop' => '',
            'csosn' => '',
            'aliquota' => 0,
            'valor_icms' => 0,
            'base_calculo' => 0,
        ];
    }
    public function atualizarTotaisItem()
    {
        $this->novoItem['subtotal'] = $this->novoItem['quantidade'] * $this->novoItem['valor_unitario'];
        $this->novoItem['total'] = $this->novoItem['subtotal'];
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

    public function selecionarProduto($id)
    {
        $produto = Produto::find($id);

        if ($produto) {
            $this->novoItem['produto'] = $produto->descricao;
            $this->novoItem['valor_unitario'] = $produto->preco_venda ?? 0;
            $this->novoItem['cst'] = $produto->cst ?? '';
            $this->novoItem['cfop'] = $produto->cfop_interno ?? '';
            $this->novoItem['csosn'] = $produto->csosn ?? '';
            $this->novoItem['aliquota'] = $produto->aliquota ?? 0;
            $this->novoItem['base_calculo'] = $produto->preco_venda ?? 0;
            $this->novoItem['valor_icms'] = ($produto->preco_venda ?? 0) * ($produto->aliquota ?? 0) / 100;
        }

        $this->modalProdutoAberto = false;
    }

    public function getSubtotalNotaProperty()
    {
        return collect($this->itens)->sum('subtotal');
    }

    public function getDescontoNotaProperty()
    {
        return collect($this->itens)->sum('desconto');
    }

    public function getAcrescimoNotaProperty()
    {
        return collect($this->itens)->sum('acrescimo');
    }

    public function getTotalNotaProperty()
    {
        return collect($this->itens)->sum(function ($i) {
            return ($i['subtotal'] ?? 0) - ($i['desconto'] ?? 0) + ($i['acrescimo'] ?? 0);
        });
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

    public function abrirModalCliente()
    {
        $this->modalClienteAberto = true;
        $this->buscaCliente = '';
        $this->clientes = Cliente::limit(20)->get()->toArray();
    }
    
    public function fecharModalCliente()
    {
        $this->modalClienteAberto = false;
    }
    
    public function selecionarCliente($id)
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $this->cliente = ['id' => $cliente->id, 'nome_razao_social' => $cliente->nome_razao_social];
        }
        $this->fecharModalCliente();
    }
    
    public function updatedBuscaCliente()
    {
        $this->clientes = Cliente::where('nome', 'like', '%' . $this->buscaCliente . '%')
            ->limit(20)->get()->toArray();
    }

    public function updatedNovoItem($value, $key)
    {
        if (in_array($key, ['quantidade', 'valor_unitario'])) {
            $this->atualizarTotaisItem();
        }
    }
    
    public function updatedFormaPagamentoDetalhada($value)
    {
        // Limpa campos específicos sempre que a forma de pagamento muda
        $this->quantidade_parcelas = null;
        $this->bandeira_cartao = null;
        $this->data_vencimento = null;

        // Se a finalidade da nota for Ajuste (3) ou Devolução (4), força "Sem Pagamento"
        if (in_array($this->finalidade, ['3', '4'])) {
            $this->forma_pagamento_detalhada = '90';
        }
    }
}
