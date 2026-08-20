<?php

namespace Tests\Feature;

use App\Models\Excursao;
use App\Models\FormaPagamento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcursaoCadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_listagem_exibe_as_excursoes_cadastradas(): void
    {
        Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response = $this->get(route('eventos.excursoes.index'));

        $response->assertOk()
            ->assertSee('Excursões cadastradas')
            ->assertSee('15/09/2026')
            ->assertSee('100.020,00');
    }

    public function test_a_listagem_ordena_as_excursoes_da_data_mais_antiga_para_a_mais_futura(): void
    {
        foreach ([
            ['data' => '2026-10-20', 'responsavel' => 'Excursão futura'],
            ['data' => '2026-09-12', 'responsavel' => 'Excursão intermediária'],
            ['data' => '2026-08-20', 'responsavel' => 'Excursão antiga'],
        ] as $dados) {
            Excursao::create([
                ...$dados,
                'qtd_pessoas' => 20,
                'valor_pessoa' => 1000,
                'status' => 'AGENDADO',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Descrição da excursão',
            ]);
        }

        $response = $this->get(route('eventos.excursoes.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Excursão antiga',
                'Excursão intermediária',
                'Excursão futura',
            ]);
    }

    public function test_a_pagina_de_cadastro_de_excursao_pode_ser_acessada(): void
    {
        $response = $this->get(route('eventos.excursoes.create'));

        $response->assertOk()
            ->assertSee('Cadastrar excursão')
            ->assertSee('Quantidade de pessoas');
    }

    public function test_a_listagem_pode_ser_filtrada_por_status(): void
    {
        foreach (['AGENDADO', 'CANCELADO'] as $status) {
            Excursao::create([
                'data' => '2026-09-15',
                'qtd_pessoas' => 40,
                'valor_pessoa' => 2500.50,
                'status' => $status,
                'responsavel' => 'Responsável '.$status,
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Excursão '.$status,
            ]);
        }

        $response = $this->get(route('eventos.excursoes.index', ['status' => 'CANCELADO']));

        $response->assertOk()
            ->assertSee('Responsável CANCELADO')
            ->assertDontSee('Responsável AGENDADO');
    }

    public function test_a_listagem_pode_ser_pesquisada_por_descricao_ou_responsavel(): void
    {
        Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Visita ao parque aquático',
        ]);
        Excursao::create([
            'data' => '2026-09-20',
            'qtd_pessoas' => 20,
            'valor_pessoa' => 1500,
            'status' => 'AGENDADO',
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Passeio escolar',
        ]);

        $this->get(route('eventos.excursoes.index', ['busca' => 'parque']))
            ->assertOk()
            ->assertSee('Maria Silva')
            ->assertDontSee('João Souza');

        $this->get(route('eventos.excursoes.index', ['busca' => 'João']))
            ->assertOk()
            ->assertSee('João Souza')
            ->assertDontSee('Maria Silva');
    }

    public function test_a_listagem_pode_ser_pesquisada_pelo_codigo_da_excursao(): void
    {
        $excursaoEncontrada = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 20,
            'valor_pessoa' => 1000,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Primeira excursão',
        ]);
        Excursao::create([
            'data' => '2026-09-16',
            'qtd_pessoas' => 30,
            'valor_pessoa' => 1500,
            'status' => 'AGENDADO',
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Segunda excursão',
        ]);

        $response = $this->get(route('eventos.excursoes.index', [
            'busca' => '#'.$excursaoEncontrada->id,
        ]));

        $response->assertOk()
            ->assertSee('Maria Silva')
            ->assertDontSee('João Souza');
    }

    public function test_a_listagem_pode_ser_filtrada_por_periodo(): void
    {
        foreach ([
            ['data' => '2026-09-10', 'responsavel' => 'Antes do período'],
            ['data' => '2026-09-15', 'responsavel' => 'Dentro do período'],
            ['data' => '2026-09-20', 'responsavel' => 'Depois do período'],
        ] as $dados) {
            Excursao::create([
                ...$dados,
                'qtd_pessoas' => 20,
                'valor_pessoa' => 1000,
                'status' => 'AGENDADO',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Descrição da excursão',
            ]);
        }

        $response = $this->get(route('eventos.excursoes.index', [
            'data_inicio' => '2026-09-12',
            'data_fim' => '2026-09-18',
        ]));

        $response->assertOk()
            ->assertSee('Dentro do período')
            ->assertDontSee('Antes do período')
            ->assertDontSee('Depois do período');
    }

    public function test_os_indicadores_consideram_apenas_excursoes_do_periodo_filtrado(): void
    {
        foreach ([
            ['data' => '2026-09-15', 'status' => 'AGENDADO', 'pessoas' => 20, 'valor' => 1000],
            ['data' => '2026-09-16', 'status' => 'REALIZADO', 'pessoas' => 10, 'valor' => 500],
            ['data' => '2026-10-20', 'status' => 'REALIZADO', 'pessoas' => 100, 'valor' => 9000],
        ] as $dados) {
            Excursao::create([
                'data' => $dados['data'],
                'qtd_pessoas' => $dados['pessoas'],
                'valor_pessoa' => $dados['valor'],
                'status' => $dados['status'],
                'responsavel' => 'Maria Silva',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Descrição da excursão',
            ]);
        }

        $response = $this->get(route('eventos.excursoes.index', [
            'data_inicio' => '2026-09-01',
            'data_fim' => '2026-09-30',
        ]));

        $response->assertOk()
            ->assertViewHas('resumo', function (array $resumo) {
                return $resumo['agendadas'] === 1
                    && $resumo['realizadas'] === 1
                    && (int) $resumo['pessoas'] === 10
                    && (float) $resumo['receita_prevista'] === 20000.0
                    && (float) $resumo['receita_realizada'] === 5000.0;
            });
    }

    public function test_uma_excursao_pode_ser_cadastrada(): void
    {
        $dinheiro = FormaPagamento::create(['descricao' => 'Dinheiro']);
        $cartao = FormaPagamento::create(['descricao' => 'Cartão']);

        $response = $this->post(route('eventos.excursoes.store'), [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
            'recebimentos' => [
                ['valor' => 30000, 'forma_pagamento_id' => $dinheiro->id],
                ['valor' => 20010, 'forma_pagamento_id' => $cartao->id],
            ],
        ]);

        $response->assertRedirect(route('eventos.excursoes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('excursoes', [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);
        $this->assertDatabaseCount('recebimento_excursao', 2);
        $this->assertDatabaseHas('recebimento_excursao', [
            'data_recebimento' => Carbon::today()->toDateString(),
            'valor' => 30000,
            'forma_pagamento_id' => $dinheiro->id,
        ]);
    }

    public function test_os_campos_obrigatorios_da_excursao_sao_validados(): void
    {
        $response = $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => '',
                'qtd_pessoas' => 0,
                'valor_pessoa' => -1,
                'responsavel' => '',
                'telefone_responsavel' => '',
                'descricao' => '',
            ]);

        $response->assertRedirect(route('eventos.excursoes.create'))
            ->assertSessionHasErrors([
                'data',
                'qtd_pessoas',
                'valor_pessoa',
                'responsavel',
                'telefone_responsavel',
                'descricao',
                'recebimentos',
            ]);
    }

    public function test_exige_pagamento_inicial_entre_cinquenta_e_cem_por_cento_do_total(): void
    {
        $forma = FormaPagamento::create(['descricao' => 'Dinheiro']);
        $dados = [
            'data' => Carbon::today()->addDay()->toDateString(),
            'qtd_pessoas' => 10,
            'valor_pessoa' => 100,
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ];

        $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), $dados + [
                'recebimentos' => [
                    ['valor' => 499.98, 'forma_pagamento_id' => $forma->id],
                ],
            ])
            ->assertSessionHasErrors('recebimentos');

        $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), $dados + [
                'recebimentos' => [
                    ['valor' => 1000.02, 'forma_pagamento_id' => $forma->id],
                ],
            ])
            ->assertSessionHasErrors('recebimentos');
    }

    public function test_exige_comprovante_quando_configurado_na_forma_de_pagamento(): void
    {
        $pix = FormaPagamento::create([
            'descricao' => 'Pix',
            'exige_comprovante' => true,
        ]);

        $response = $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => Carbon::today()->addDay()->toDateString(),
                'qtd_pessoas' => 10,
                'valor_pessoa' => 100,
                'responsavel' => 'Maria Silva',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Excursão escolar',
                'recebimentos' => [
                    ['valor' => 500, 'forma_pagamento_id' => $pix->id],
                ],
            ]);

        $response->assertSessionHasErrors('recebimentos.0.comprovante');
    }

    public function test_impede_cadastro_de_excursao_com_data_passada(): void
    {
        $forma = FormaPagamento::create(['descricao' => 'Dinheiro']);

        $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => Carbon::today()->subDay()->toDateString(),
                'qtd_pessoas' => 10,
                'valor_pessoa' => 100,
                'responsavel' => 'Maria Silva',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => 'Excursão escolar',
                'recebimentos' => [
                    ['valor' => 500, 'forma_pagamento_id' => $forma->id],
                ],
            ])
            ->assertSessionHasErrors('data');
    }

    public function test_a_descricao_da_excursao_tem_limite_de_200_caracteres(): void
    {
        $response = $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => '2026-09-15',
                'qtd_pessoas' => 40,
                'valor_pessoa' => 2500.50,
                'responsavel' => 'Maria Silva',
                'telefone_responsavel' => '(11) 99999-9999',
                'descricao' => str_repeat('a', 201),
            ]);

        $response->assertRedirect(route('eventos.excursoes.create'))
            ->assertSessionHasErrors('descricao');
    }

    public function test_a_excursao_e_exibida_no_periodo_do_planner(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response = $this->getJson(route('eventos.planner.eventos', [
            'start' => '2026-09-01',
            'end' => '2026-10-01',
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => 'excursao-'.$excursao->id,
                'title' => 'Excursão - 40 pessoas',
                'start' => '2026-09-15',
            ]);
    }

    public function test_uma_excursao_pode_ser_editada(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response = $this->put(route('eventos.excursoes.update', $excursao), [
            'data' => '2026-09-20',
            'qtd_pessoas' => 45,
            'valor_pessoa' => 3000,
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Excursão empresarial atualizada',
        ]);

        $response->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'data' => '2026-09-20',
            'qtd_pessoas' => 45,
            'valor_pessoa' => 3000,
            'status' => 'AGENDADO',
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Excursão empresarial atualizada',
        ]);
    }

    public function test_ao_excluir_uma_excursao_seu_status_e_alterado_para_cancelado(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão cancelada',
        ]);

        $response = $this->delete(route('eventos.excursoes.destroy', $excursao));

        $response->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'status' => 'CANCELADO',
        ]);
    }

    public function test_uma_excursao_pode_ser_iniciada_e_finalizada(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $this->patch(route('eventos.excursoes.start', $excursao))
            ->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'status' => 'EM_ANDAMENTO',
        ]);

        $this->patch(route('eventos.excursoes.finish', $excursao))
            ->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'status' => 'REALIZADO',
        ]);
    }

    public function test_uma_excursao_finalizada_nao_pode_ser_alterada_ou_excluida(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'REALIZADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão finalizada',
        ]);

        $dadosAlterados = [
            'data' => '2026-10-01',
            'qtd_pessoas' => 50,
            'valor_pessoa' => 3000,
            'responsavel' => 'Outro responsável',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Tentativa de alteração',
        ];

        $this->put(route('eventos.excursoes.update', $excursao), $dadosAlterados)
            ->assertSessionHas('error');
        $this->delete(route('eventos.excursoes.destroy', $excursao))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'data' => '2026-09-15',
            'status' => 'REALIZADO',
        ]);
    }

    public function test_uma_excursao_cancelada_so_pode_ser_visualizada(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor_pessoa' => 2500.50,
            'status' => 'CANCELADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão cancelada',
        ]);

        $this->get(route('eventos.excursoes.edit', $excursao))
            ->assertRedirect(route('eventos.excursoes.index'));

        $this->put(route('eventos.excursoes.update', $excursao), [
            'data' => '2026-10-01',
            'qtd_pessoas' => 50,
            'valor_pessoa' => 3000,
            'responsavel' => 'Outro responsável',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Tentativa de alteração',
        ])->assertSessionHas('error');

        $this->delete(route('eventos.excursoes.destroy', $excursao))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'data' => '2026-09-15',
            'status' => 'CANCELADO',
        ]);
    }
}
