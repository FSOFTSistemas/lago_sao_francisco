<?php

namespace Tests\Feature;

use App\Models\Excursao;
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
            'valor' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response = $this->get(route('eventos.excursoes.index'));

        $response->assertOk()
            ->assertSee('Excursões cadastradas')
            ->assertSee('15/09/2026')
            ->assertSee('2.500,50');
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
                'valor' => 2500.50,
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

    public function test_uma_excursao_pode_ser_cadastrada(): void
    {
        $response = $this->post(route('eventos.excursoes.store'), [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response->assertRedirect(route('eventos.excursoes.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('excursoes', [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);
    }

    public function test_os_campos_obrigatorios_da_excursao_sao_validados(): void
    {
        $response = $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => '',
                'qtd_pessoas' => 0,
                'valor' => -1,
                'status' => 'INVALIDO',
                'responsavel' => '',
                'telefone_responsavel' => '',
                'descricao' => '',
            ]);

        $response->assertRedirect(route('eventos.excursoes.create'))
            ->assertSessionHasErrors([
                'data',
                'qtd_pessoas',
                'valor',
                'status',
                'responsavel',
                'telefone_responsavel',
                'descricao',
            ]);
    }

    public function test_a_excursao_e_exibida_no_periodo_do_planner(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
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
            'valor' => 2500.50,
            'status' => 'AGENDADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão escolar',
        ]);

        $response = $this->put(route('eventos.excursoes.update', $excursao), [
            'data' => '2026-09-20',
            'qtd_pessoas' => 45,
            'valor' => 3000,
            'status' => 'REALIZADO',
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Excursão empresarial atualizada',
        ]);

        $response->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseHas('excursoes', [
            'id' => $excursao->id,
            'data' => '2026-09-20',
            'qtd_pessoas' => 45,
            'valor' => 3000,
            'status' => 'REALIZADO',
            'responsavel' => 'João Souza',
            'telefone_responsavel' => '(11) 98888-8888',
            'descricao' => 'Excursão empresarial atualizada',
        ]);
    }

    public function test_uma_excursao_pode_ser_excluida(): void
    {
        $excursao = Excursao::create([
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
            'status' => 'CANCELADO',
            'responsavel' => 'Maria Silva',
            'telefone_responsavel' => '(11) 99999-9999',
            'descricao' => 'Excursão cancelada',
        ]);

        $response = $this->delete(route('eventos.excursoes.destroy', $excursao));

        $response->assertRedirect(route('eventos.excursoes.index'));
        $this->assertDatabaseMissing('excursoes', ['id' => $excursao->id]);
    }
}
