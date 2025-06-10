<?php

namespace App\Livewire;

use App\Http\Controllers\NotaFiscalController;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NFeNew extends Component
{

    // Propriedade pública para o campo CFOP individual
    public $cfop;
    // Propriedades para controle do modal de CFOP
    public $modalCfopAberto = false;
    public $buscaCfop = '';
    // Lista filtrada de CFOPs para exibir no modal
    public $cfops = [];

    public $empresa = 'FSOFT SISTEMAS';
    public $numero;
    public $serie;
    public $data_emissao;
    public $data_saida;
    public $tipo_nota = 'saida';
    public $finalidade;
    public $keyCliente;
    public $keyProd;
    public $forma_pagamento;
    public $cliente = ['id' => null, 'razao_social' => 'Consumidor'];
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
        $this->cfop = '';
    }
    public function atualizarTotaisItem()
    {
        $sub = $this->novoItem['quantidade'] * $this->novoItem['valor_unitario']; 
        $this->novoItem['subtotal'] = $sub;
        $this->novoItem['total'] = $this->novoItem['subtotal'];
        $this->novoItem['base_calculo'] = $this->novoItem['quantidade'] * $this->novoItem['valor_unitario'];
        $this->novoItem['valor_icms'] = ($sub ?? 1) * ($this->novoItem['aliquota'] ?? 1) / 100;

        $this->keyProd = now()->timestamp;
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
            $this->novoItem['subtotal'] = $produto->preco_venda * 1;
        }

        $this->keyProd = now()->timestamp;
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
            $this->cliente = ['id' => $cliente->id, 'razao_social' => $cliente->nome_razao_social];
            $this->keyCliente = now()->timestamp;
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

    

    public function salvarNfe()
    {
        $dados = [
            'empresa' => $this->empresa,
            'numero' => $this->numero,
            'serie' => $this->serie,
            'data_emissao' => $this->data_emissao,
            'data_saida' => $this->data_saida,
            'tipo_nota' => $this->tipo_nota,
            'finalidade' => $this->finalidade,
            'forma_pagamento' => $this->forma_pagamento,
            'forma_pagamento_detalhada' => $this->forma_pagamento_detalhada,
            'quantidade_parcelas' => $this->quantidade_parcelas,
            'bandeira_cartao' => $this->bandeira_cartao,
            'data_vencimento' => $this->data_vencimento,
            'cliente' => $this->cliente,
            'itens' => $this->itens,
        ];

        $request = new Request($dados);
        $controller = new NotaFiscalController();
        return $controller->store($request);
    }

    
    // Métodos para abrir e fechar o modal de CFOP
    public function abrirModalCfop()
    {
        $this->modalCfopAberto = true;
        $this->buscaCfop = '';
        $this->filtrarCfops();
    }

    public function fecharModalCfop()
    {
        $this->modalCfopAberto = false;
    }

    public function filtrarCfops()
    {
        $todosCfops = [
            ['codigo' => '5101', 'descricao' => 'Venda de produção do estabelecimento'],
            ['codigo' => '5405', 'descricao' => 'Venda de mercadoria adquirida ou recebida de terceiros'],
            ['codigo' => '6101', 'descricao' => 'Venda de produção do estabelecimento (fora do estado)'],
            ['codigo' => '6108', 'descricao' => 'Venda de mercadoria recebida de terceiros (fora do estado)'],
            ['codigo' => '5929', 'descricao' => 'Lançamento efetuado a título de simples faturamento decorrente de venda para entrega futura'],
            // ...adicione outros conforme necessário
        ];

        $busca = strtolower($this->buscaCfop);
        $this->cfops = array_filter($todosCfops, function ($cfop) use ($busca) {
            return str_contains(strtolower($cfop['codigo']), $busca) || str_contains(strtolower($cfop['descricao']), $busca);
        });
    }

    public function updatedBuscaCfop()
    {
        $this->filtrarCfops();
    }

    public function selecionarCfop($codigo)
    {
        $this->cfop = $codigo;
        $this->fecharModalCfop();
    }
}

