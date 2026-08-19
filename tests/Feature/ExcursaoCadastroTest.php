<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcursaoCadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pagina_de_cadastro_de_excursao_pode_ser_acessada(): void
    {
        $response = $this->get(route('eventos.excursoes.create'));

        $response->assertOk()
            ->assertSee('Cadastrar excursão')
            ->assertSee('Quantidade de pessoas');
    }

    public function test_uma_excursao_pode_ser_cadastrada(): void
    {
        $response = $this->post(route('eventos.excursoes.store'), [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
        ]);

        $response->assertRedirect(route('eventos.excursoes.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('excursoes', [
            'data' => '2026-09-15',
            'qtd_pessoas' => 40,
            'valor' => 2500.50,
        ]);
    }

    public function test_os_campos_obrigatorios_da_excursao_sao_validados(): void
    {
        $response = $this->from(route('eventos.excursoes.create'))
            ->post(route('eventos.excursoes.store'), [
                'data' => '',
                'qtd_pessoas' => 0,
                'valor' => -1,
            ]);

        $response->assertRedirect(route('eventos.excursoes.create'))
            ->assertSessionHasErrors(['data', 'qtd_pessoas', 'valor']);
    }
}
