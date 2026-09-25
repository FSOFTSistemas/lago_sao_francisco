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
    // Propriedade para identificação da empresa
    public $empresa_id;
    public $empresa = 'FSOFT SISTEMAS';

    // Cabeçalho da nota
    public $numero;
    public $serie;
    public $data_emissao;
    public $data_saida;
    public $tipo_nota = '1';
    public $finalidade = '1';
    public $natureza_operacao = 'VENDA DE MERCADORIA';
    public $cfop = '5102';

    // Controle de modais
    public $modalCfopAberto = false;
    public $buscaCfop = '';
    public $cfops = [];

    public $keyCliente;
    public $keyProd;

    // Cliente
    public $cliente = ['id' => null, 'razao_social' => 'Consumidor', 'cpf_cnpj' => '', 'rg_ie' => ''];
    public $modalClienteAberto = false;
    public $buscaCliente = '';
    public $clientes = [];

    // Faturamento e Pagamento
    public $forma_pagamento = '0';
    public $forma_pagamento_detalhada = '01';
    public $quantidade_parcelas;
    public $bandeira_cartao;
    public $data_vencimento;

    // Informações adicionais e referenciada
    public $informacoes_complementares = '';
    public $chave_nfe_referenciada = '';

    // Abas de navegação
    public $aba = 'itens';

    // Controle do item
    public $modalAberto = false;
    public $mostrarTributaria = false;

    public $modalProdutoAberto = false;
    public $buscaProduto = '';
    public $produtos = [];

    public $novoItem = [
        'produto_id' => null,
        'produto' => '',
        'quantidade' => 1,
        'valor_unitario' => 0,
        'subtotal' => 0,
        'desconto' => 0,
        'acrescimo' => 0,
        'total' => 0,
        'ncm' => '',
        'cfop' => '',
        'cst' => '',
        'csosn' => '102',
        'un' => 'UN',
        'ean' => 'SEM GTIN',
        'aliquota' => 0,
        'valor_icms' => 0,
        'base_calculo' => 0,
    ];

    public $itens = [];

    public function mount()
    {
        $user = Auth::user();
        $this->empresa_id = $user ? $user->empresa_id : 1;

        try {
            $empresaObj = Empresa::with('preferencia')->find($this->empresa_id);
            $this->empresa = $empresaObj ? $empresaObj->razao_social : 'EMPRESA';
            $this->serie = (int) ($empresaObj?->preferencia?->serie ?: 1);
            $this->numero = (int) (($empresaObj?->preferencia?->numero_ultima_nota ?: 0) + 1);
            $this->cfop = $empresaObj?->preferencia?->cfop_padrao ?: '5102';
            $this->produtos = Produto::where('empresa_id', $this->empresa_id)->get()->toArray();
        } catch (\Throwable $e) {
            $this->empresa = 'EMPRESA PADRAO';
            $this->serie = 1;
            $this->numero = 1;
            $this->cfop = '5102';
            $this->produtos = [];
        }

        $this->data_emissao = now()->toDateString();
        $this->data_saida = now()->toDateString();
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
        if (empty($this->novoItem['produto'])) {
            session()->flash('error', 'Selecione um produto antes de salvar o item.');
            return;
        }

        if (((float) ($this->novoItem['quantidade'] ?? 0)) <= 0) {
            session()->flash('error', 'A quantidade do item deve ser maior que zero.');
            return;
        }

        $this->atualizarTotaisItem();
        $this->itens[] = $this->novoItem;
        $this->fecharModal();
    }

    public function removerItem($index)
    {
        if (isset($this->itens[$index])) {
            unset($this->itens[$index]);
            $this->itens = array_values($this->itens);
        }
    }

    public function resetNovoItem()
    {
        $this->novoItem = [
            'produto_id' => null,
            'produto' => '',
            'quantidade' => 1,
            'valor_unitario' => 0,
            'subtotal' => 0,
            'desconto' => 0,
            'acrescimo' => 0,
            'total' => 0,
            'ncm' => '',
            'cfop' => $this->cfop ?: '5102',
            'cst' => '',
            'csosn' => '102',
            'un' => 'UN',
            'ean' => 'SEM GTIN',
            'aliquota' => 0,
            'valor_icms' => 0,
            'base_calculo' => 0,
        ];
    }

    public function atualizarTotaisItem()
    {
        $qtd = (float) ($this->novoItem['quantidade'] ?? 1);
        $vUnit = (float) ($this->novoItem['valor_unitario'] ?? 0);
        $desc = (float) ($this->novoItem['desconto'] ?? 0);
        $acresc = (float) ($this->novoItem['acrescimo'] ?? 0);

        $sub = round($qtd * $vUnit, 2);
        $this->novoItem['subtotal'] = $sub;
        $this->novoItem['total'] = max(0, round($sub - $desc + $acresc, 2));
        $this->novoItem['base_calculo'] = $this->novoItem['total'];

        $aliq = (float) ($this->novoItem['aliquota'] ?? 0);
        $this->novoItem['valor_icms'] = round($this->novoItem['base_calculo'] * ($aliq / 100), 2);

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
            $this->novoItem['produto_id'] = $produto->id;
            $this->novoItem['produto'] = $produto->descricao;
            $this->novoItem['valor_unitario'] = (float) ($produto->preco_venda ?? 0);
            $this->novoItem['cst'] = $produto->cst ?? '';
            $this->novoItem['cfop'] = $this->cfop ?: ($produto->cfop_interno ?: '5102');
            $this->novoItem['csosn'] = $produto->csosn ?: '102';
            $this->novoItem['ncm'] = $produto->ncm ?: '21069090';
            $this->novoItem['un'] = 'UN';
            $this->novoItem['ean'] = $produto->ean ?: 'SEM GTIN';
            $this->novoItem['aliquota'] = (float) ($produto->aliquota ?? 0);
            $this->novoItem['desconto'] = 0;
            $this->novoItem['acrescimo'] = 0;

            $this->atualizarTotaisItem();
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
        $busca = trim((string) $this->buscaProduto);
        $produtosFiltrados = collect($this->produtos)
            ->filter(function ($produto) use ($busca) {
                if (empty($busca)) {
                    return true;
                }
                $descricao = $produto['descricao'] ?? $produto['nome'] ?? '';
                return stripos($descricao, $busca) !== false;
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
            $this->cliente = [
                'id' => $cliente->id,
                'razao_social' => $cliente->nome_razao_social,
                'cpf_cnpj' => $cliente->cpf_cnpj,
                'rg_ie' => $cliente->rg_ie,
            ];
            $this->keyCliente = now()->timestamp;
        }
        $this->fecharModalCliente();
    }

    public function updatedBuscaCliente()
    {
        $this->clientes = Cliente::where('nome_razao_social', 'like', '%' . $this->buscaCliente . '%')
            ->orWhere('apelido_nome_fantasia', 'like', '%' . $this->buscaCliente . '%')
            ->orWhere('cpf_cnpj', 'like', '%' . $this->buscaCliente . '%')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function updatedNovoItem($value, $key)
    {
        if (in_array($key, ['quantidade', 'valor_unitario', 'desconto', 'acrescimo', 'aliquota'])) {
            $this->atualizarTotaisItem();
        }
    }

    public function updatedFormaPagamentoDetalhada($value)
    {
        $this->quantidade_parcelas = null;
        $this->bandeira_cartao = null;
        $this->data_vencimento = null;

        if (in_array($this->finalidade, ['3', '4'])) {
            $this->forma_pagamento_detalhada = '90';
        }
    }

    public function salvarNfe()
    {
        // Validações no Livewire antes do envio
        if (empty($this->itens)) {
            session()->flash('error', 'Adicione pelo menos um item à nota fiscal antes de salvar.');
            return;
        }

        if (empty($this->cliente['id'])) {
            session()->flash('error', 'Selecione um cliente para a nota fiscal.');
            return;
        }

        if (empty($this->numero)) {
            session()->flash('error', 'Informe o número da nota fiscal.');
            return;
        }

        $dados = [
            'empresa_id' => $this->empresa_id,
            'empresa' => $this->empresa,
            'numero' => (int) $this->numero,
            'serie' => (int) $this->serie,
            'data_emissao' => $this->data_emissao,
            'data_saida' => $this->data_saida,
            'tipo_nota' => $this->tipo_nota,
            'finalidade' => $this->finalidade,
            'cfop' => $this->cfop,
            'natureza_operacao' => $this->natureza_operacao,
            'forma_pagamento' => $this->forma_pagamento,
            'forma_pagamento_detalhada' => $this->forma_pagamento_detalhada,
            'quantidade_parcelas' => $this->quantidade_parcelas,
            'bandeira_cartao' => $this->bandeira_cartao,
            'data_vencimento' => $this->data_vencimento,
            'informacoes_complementares' => $this->informacoes_complementares,
            'chave_nfe_referenciada' => $this->chave_nfe_referenciada,
            'nfe_referenciada' => $this->chave_nfe_referenciada,
            'cliente' => $this->cliente,
            'itens' => $this->itens,
            'subtotal' => $this->subtotalNota,
            'desconto' => $this->descontoNota,
            'total' => $this->totalNota,
        ];

        $request = new Request($dados);
        $controller = app(NotaFiscalController::class);
        return $controller->store($request);
    }

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
            ['codigo' => '5102', 'descricao' => 'Venda de mercadoria adquirida ou recebida de terceiros'],
            ['codigo' => '5405', 'descricao' => 'Venda de mercadoria adquirida ou recebida de terceiros sujeita a ST'],
            ['codigo' => '6101', 'descricao' => 'Venda de produção do estabelecimento (fora do estado)'],
            ['codigo' => '6102', 'descricao' => 'Venda de mercadoria de terceiros (fora do estado)'],
            ['codigo' => '6108', 'descricao' => 'Venda de mercadoria para consumidor final não contribuinte (fora do estado)'],
            ['codigo' => '5929', 'descricao' => 'Lançamento efetuado a título de simples faturamento decorrente de cupom fiscal'],
            ['codigo' => '5933', 'descricao' => 'Prestação de serviço tributado pelo ISSQN'],
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
        $this->novoItem['cfop'] = $codigo;
        $this->fecharModalCfop();
    }
}
