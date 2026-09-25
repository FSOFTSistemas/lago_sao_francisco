<?php

namespace Tests\Unit;

use App\Livewire\NFeNew;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NFeNewTest extends TestCase
{
    public function test_componente_monta_dados_padrao_e_proximo_numero(): void
    {
        $user = new User(['id' => 99, 'empresa_id' => 1]);
        Auth::setUser($user);

        $component = new NFeNew();
        $component->mount();

        $this->assertEquals(1, $component->empresa_id);
        $this->assertNotEmpty($component->data_emissao);
        $this->assertNotEmpty($component->data_saida);
        $this->assertGreaterThanOrEqual(1, $component->numero);
        $this->assertGreaterThanOrEqual(1, $component->serie);
    }

    public function test_selecionar_produto_preenche_todos_atributos_fiscais(): void
    {
        $produto = new Produto([
            'id'           => 42,
            'descricao'    => 'REFEICAO COMPLETA ALMOCO',
            'preco_venda'  => 35.50,
            'ncm'          => '21069090',
            'cfop_interno' => '5102',
            'csosn'        => '102',
            'cst'          => '00',
            'aliquota'     => 18.0,
            'ean'          => '7891234567890',
        ]);
        $produto->id = 42;

        $component = new NFeNew();
        $component->novoItem['produto_id'] = $produto->id;
        $component->novoItem['produto'] = $produto->descricao;
        $component->novoItem['valor_unitario'] = (float) $produto->preco_venda;
        $component->novoItem['ncm'] = $produto->ncm;
        $component->novoItem['un'] = 'UN';
        $component->novoItem['cfop'] = $produto->cfop_interno;
        $component->novoItem['csosn'] = $produto->csosn;
        $component->novoItem['aliquota'] = $produto->aliquota;
        $component->novoItem['quantidade'] = 2;
        $component->atualizarTotaisItem();

        $this->assertEquals(42, $component->novoItem['produto_id']);
        $this->assertEquals('REFEICAO COMPLETA ALMOCO', $component->novoItem['produto']);
        $this->assertEquals('21069090', $component->novoItem['ncm']);
        $this->assertEquals('UN', $component->novoItem['un']);
        $this->assertEquals('5102', $component->novoItem['cfop']);
        $this->assertEquals('102', $component->novoItem['csosn']);
        $this->assertEquals(71.0, $component->novoItem['subtotal']);
        $this->assertEquals(71.0, $component->novoItem['total']);
        $this->assertEquals(12.78, $component->novoItem['valor_icms']);
    }

    public function test_bloqueia_salvar_sem_itens(): void
    {
        $component = new NFeNew();
        $component->itens = [];
        $component->cliente = ['id' => 1, 'razao_social' => 'CLIENTE TESTE'];

        $component->salvarNfe();

        $this->assertEquals('Adicione pelo menos um item à nota fiscal antes de salvar.', session('error'));
    }

    public function test_bloqueia_salvar_sem_cliente(): void
    {
        $component = new NFeNew();
        $component->itens = [['produto' => 'Item 1', 'quantidade' => 1, 'valor_unitario' => 10, 'subtotal' => 10, 'total' => 10]];
        $component->cliente = ['id' => null, 'razao_social' => ''];

        $component->salvarNfe();

        $this->assertEquals('Selecione um cliente para a nota fiscal.', session('error'));
    }
}
